<?php

namespace App\Http\Controllers\API\V2;

use stdClass;
use Throwable;
use App\Models\User;
use App\Models\Recipe;
use App\Models\MealType;
use App\Models\StepsGoal;
use App\Models\UserRecipe;
use App\Models\FoodPlanner;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\TaskMilestone;
use App\Models\userMilestone;
use App\Models\FavouriteRecipe;
use App\Models\RecipeNutrition;
use PhpParser\Node\Stmt\Catch_;
use App\Models\UserLevelTaskLog;
use App\Models\NutritionFavourite;
use Illuminate\Support\Facades\DB;
use App\Models\NutritionIngredient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Models\NutritionIngredientUnit;
use App\Models\UsersCurrentMeasurement;
use App\Models\UsersInitialMeasurement;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\NutritionIngredientBarcode;
use App\Http\Controllers\API\ApiController;
use App\Models\NutritionIngredientCategory;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB as FacadesDB;
use App\Http\Controllers\API\FoodPlanerController as APIFoodPlanerControllerV1;
use OpenAI;

class FoodPlanerController extends APIFoodPlanerControllerV1
{
    public function dailyFoodPlanner(Request $request)
    {
        try {
            $data = $request->all();

            // Check if meal type is water and handle it separately
            if ($data['meal_type'] === 'water') {
                return $this->handleWater($data);
            }

            // Validate the input data
            $validator = Validator::make($data, $this->validationRules());
            if ($validator->fails()) {
                return $this->validationError("Fail", $validator->errors()->first(), 200);
            }

            // Fetch recipes and ingredients data
            $combinedData = $this->getCombinedData($data);
            // Validate if the provided nutrition details match the available data
            $matches = $this->validateNutritionDetails($data['nutritiondetails'], $combinedData);
            if (empty($matches)) {
                return $this->errorResponse("Recipe or Ingredient does not match with nutrition list.", [], 200);
            }
            // Update or create food planner entries
            $savedData = $this->updateOrCreateFoodPlannerEntries($data, $matches);
            return $this->getSingleResponse("Food added to your plan", $savedData, 200);
        } catch (Throwable $th) {
            // dd($th->getMessage());
            return $this->throwableError("Something went wrong", $th->getMessage(), 500);
        }
    }

    protected function handleWater(array $data)
    {
        $userId = Auth::user()->id;
        $waterQuantity = 250;
        $existingWaterEntry = FoodPlanner::where([
            'meal_type' => 'water',
            'date' => $data['date'],
            'user_id' => $userId
        ])->first();

        if ($existingWaterEntry) {
            if ($existingWaterEntry->water_consume + $waterQuantity > 2500) {
                $savedData = $this->getFoodPlannerData($data['date']);
                return $this->getSingleResponse("Water consumption limit reached", $savedData, 400);
            }
            $existingWaterEntry->water_consume += $waterQuantity;
            $existingWaterEntry->save();
        } else {
            $foodPlanner = new FoodPlanner;
            $foodPlanner->user_id = $userId;
            $foodPlanner->recipe_or_ingredient_id = null;
            $foodPlanner->meal_type = 'water';
            $foodPlanner->date = $data['date'];
            $foodPlanner->portion = 0;
            $foodPlanner->kcal = null;
            $foodPlanner->water_consume = $waterQuantity;
            $foodPlanner->no_of_servings = 1;
            $foodPlanner->is_ingredient = 2;
            $foodPlanner->ai_Food_name = "";
            $foodPlanner->save();
        }
        $levelDetails = null;
        $levelResponse = $this->logLevelTaskEntries($userId, 'drink-water',1,0);


        if ($levelResponse != 0) {
            $levelDetails = $this->getLevelDetails($levelResponse);
        }

        $milestoneCompletedArray = [];

        $drinkWaterMilestones = TaskMilestone::where('milestone_type', 'drink-water')->get();

        $userCurrentMilestones = userMilestone::where([
            'user_id' => $userId,
            'milestone_type' => 'drink-water'
        ])->pluck('milestone_id')->toArray();

        $userStreakCompleteCount = UserLevelTaskLog::where([
            'user_id' => $userId,
            'level_task_type' => 'drink-water'
        ])->sum('completed_count');

        foreach ($drinkWaterMilestones as $milestone) {

            if (
                $userStreakCompleteCount >= $milestone->milestone_count &&

                !in_array($milestone->id, $userCurrentMilestones)
            ) {
                $milestoneCompletedArray[] = [
                    'type' => 'drink-water',
                    'color' => 'blue',
                    'title' => $milestone->milestone_title,
                    'description' => $milestone->milestone_description,
                    'count' => $milestone->milestone_count,
                ];

                // Save to user_milestones table
                UserMilestone::create([
                    'user_id' => $userId,
                    'milestone_id' => $milestone->id,
                    'milestone_type' => 'drink-water',
                ]);
            }
        }

        $savedData = $this->getFoodPlannerData($data['date'], $milestoneCompletedArray, $levelDetails);
        return $this->getSingleResponse("Water quantity updated", $savedData, 200);
    }


    protected function getCombinedData($payload)
    {
        try {
            $ids = array_column($payload['nutritiondetails'], 'recipe_or_ingredient_id');
            //AI data fetch
            $AiDataFetched=[];
            foreach ($payload['nutritiondetails'] as $AIdata) {
                if ($AIdata['is_ingredient'] && $AIdata['is_ingredient']== 4) {
                    $AiDataFetched[]=[
                        'recipe_or_ingredient_id'=>$AIdata['recipe_or_ingredient_id'],
                        'name'=>$AIdata['name'],
                        'is_ingredient'=>$AIdata['is_ingredient'],
                        'kcal'=>$AIdata['kcal'],
                        'protein'=>$AIdata['protein'],
                        'fats'=>$AIdata['fats'],
                        'carbs'=>$AIdata['carbs'],
                        'quantity'=>$AIdata['quantity'],
                        'no_of_servings'=>$AIdata['no_of_servings'],
                    ];
                }
            }
            $recipes = Recipe::with('nutrition')->whereIn('id', $ids)->get()->toArray();
            $recipeData = array_map(fn($recipe) => [
                "recipe_or_ingredient_id" => $recipe['id'],
                "title" => $recipe['title'],
                "image" => asset(Storage::url($recipe['picture'])),
                "is_ingredient" => 0
            ], $recipes);

            $ingredients = NutritionIngredient::select('id', 'name', 'small_image_url')->whereIn('id', $ids)->get()->toArray();
            $ingredientData = array_map(fn($ingredient) => [
                "recipe_or_ingredient_id" => $ingredient['id'],
                "title" => $ingredient['name'],
                "image" => $ingredient['small_image_url'] ?? '',
                "is_ingredient" => 1
            ], $ingredients);

            $userRecipes = UserRecipe::select('id', 'title', 'image')->whereIn('id', $ids)->get()->toArray();
            $userRecipesData = array_map(fn($userRecipe) => [
                "recipe_or_ingredient_id" => $userRecipe['id'],
                "title" => $userRecipe['title'],
                "image" => $userRecipe['image'] ? asset(Storage::url($userRecipe['image'])) : '',
                "is_ingredient" => 3
            ], $userRecipes);

            return array_merge($recipeData, $ingredientData, $userRecipesData, $AiDataFetched);
        } catch (\Exception $e) {
            Log::error('Error in yourMethod: ' . $e->getMessage());

            return response()->json(['error' => 'An error occurred while fetching data.'], 500);
        }
    }

    protected function validateNutritionDetails(array $nutritionDetails, array $combinedData)
    {
        return array_filter($combinedData, function ($nutritionData) use ($nutritionDetails) {
            foreach ($nutritionDetails as $nutritionValue) {
                if ($nutritionValue['is_ingredient'] == 4 && $nutritionData['is_ingredient'] == $nutritionValue['is_ingredient']) {
                    if ($nutritionData['name']==$nutritionValue['name']) {
                        return true;
                    }
                }
                if ($nutritionData['recipe_or_ingredient_id'] == $nutritionValue['recipe_or_ingredient_id'] && $nutritionData['is_ingredient'] == $nutritionValue['is_ingredient']) {
                    return true;
                }
            }
            return false;
        });
    }

    protected function updateOrCreateFoodPlannerEntries(array $data, array $matches)
    {
        $levelDetails = null;
        $filtered = array_filter($data['nutritiondetails'], function ($item) use ($matches) {
            foreach ($matches as $match) {
                if ($match['is_ingredient'] == 4 && $item['is_ingredient'] == $match['is_ingredient']) {
                    if ($item['name']==$match['name']) {
                        return true;
                    }
                }
                if ($item['recipe_or_ingredient_id'] === $match['recipe_or_ingredient_id'] &&
                    $item['is_ingredient'] === $match['is_ingredient']
                ) {
                    return true;
                }
            }
            return false;
        });
        // Group the filtered data by is_ingredient
        $grouped = collect($filtered)->groupBy('is_ingredient');
        // Store each group in a separate variable
        $filteredIngredients = $grouped->get(1, []); // is_ingredient = 1
        $filteredRecipes = $grouped->get(0, []);     // is_ingredient = 0
        $filteredUserRecipes = $grouped->get(3, []); // is_ingredient = 3
        $filteredUserAI = $grouped->get(4, []); // is_ingredient = 4
        // Check if this is the first time logging for the meal_type today
        $firstLogged = FoodPlanner::where('user_id', Auth::user()->id)
        ->whereRaw('LOWER(meal_type) = ?', [strtolower($data['meal_type'])])
        ->whereDate('date', $data['date'])
        ->count() === 0;


        foreach ($filteredUserAI as $key => $foodPlannerData_AI) {
            $kcal=  $foodPlannerData_AI['kcal']?? 0;
            $carbs= $foodPlannerData_AI['carbs']?? 0;
            $fats= $foodPlannerData_AI['fats']?? 0;
            $proteins= $foodPlannerData_AI['protein']?? 0;
            $no_of_servings= $foodPlannerData_AI['no_of_servings'];
            $existingEntry = FoodPlanner::where([
                'is_ingredient' => $foodPlannerData_AI['is_ingredient'],
                'meal_type' => $data['meal_type'],
                "AI_food_name"=>$foodPlannerData_AI['name'],
                'date' => $data['date'],
                'user_id' => Auth::user()->id
            ])->first();
            if ($existingEntry) {
                $existingEntry->kcal += $kcal;
                $existingEntry->carbs += $carbs;
                $existingEntry->fats += $fats;
                $existingEntry->proteins += $proteins;
                $existingEntry->portion += $foodPlannerData_AI['quantity'];
                $existingEntry->no_of_servings += $no_of_servings;
                $existingEntry->save();
            } else {
                $foodPlanner = new FoodPlanner;
                $foodPlanner->user_id = Auth::user()->id;
                $foodPlanner->meal_type = $data['meal_type'];
                $foodPlanner->date = $data['date'];
                $foodPlanner->recipe_or_ingredient_id = $foodPlannerData_AI['recipe_or_ingredient_id']??0;
                $foodPlanner->AI_food_name =$foodPlannerData_AI['name'];
                $foodPlanner->portion = $foodPlannerData_AI['quantity'];
                $foodPlanner->kcal = $kcal;
                $foodPlanner->carbs = $carbs;
                $foodPlanner->fats = $fats;
                $foodPlanner->proteins = $proteins;
                $foodPlanner->no_of_servings = $no_of_servings;
                $foodPlanner->is_ingredient = $foodPlannerData_AI['is_ingredient'];
                $foodPlanner->save();
                if ($firstLogged) {
                    $levelResponse =  $this->logDailyMealTypeTaskIfFirstTime(Auth::user()->id, $data['meal_type'], $data['date']);
                    if ($levelResponse != 0) {
                        $levelDetails = $this->getLevelDetails($levelResponse);
                    }
                    $firstLogged = false; // Prevent calling again for subsequent entries
                }
            }
            if ($proteins > 0) {
                $levelResponse = $this->logLevelTaskEntries(Auth::user()->id, 'protiens', $proteins, 0);
                if ($levelResponse != 0) {
                    $levelDetails = $this->getLevelDetails($levelResponse);
                }
            }
        }
        foreach ($filteredIngredients as $foodPlannerData_I) {
            // Calorie Calculation from request
            $ingredientData = NutritionIngredient::where(['id' => $foodPlannerData_I['recipe_or_ingredient_id']])->first();

            if (isset($foodPlannerData_I['quantity']) && !empty($foodPlannerData_I['quantity'])) {
                if (!is_null($ingredientData) && !empty($ingredientData)) {
                    $kcal = ($foodPlannerData_I['quantity'] * $ingredientData['kcal']) / 100;
                    $carbs = ($foodPlannerData_I['quantity'] * $ingredientData['carbs']) / 100;
                    $fats = ($foodPlannerData_I['quantity'] * $ingredientData['fats']) / 100;
                    $proteins = ($foodPlannerData_I['quantity'] * $ingredientData['protein']) / 100;
                } else {
                    $kcal = 0;
                    $carbs = 0;
                    $fats = 0;
                    $proteins = 0;
                }

            }else{

                $kcal = 0;
                $carbs = 0;
                $fats = 0;
                $proteins = 0;
            }

            $existingEntry = FoodPlanner::where([
                'recipe_or_ingredient_id' => $foodPlannerData_I['recipe_or_ingredient_id'],
                'is_ingredient' => $foodPlannerData_I['is_ingredient'],
                'meal_type' => $data['meal_type'],
                'date' => $data['date'],
                'user_id' => Auth::user()->id
            ])->first();

            if ($existingEntry) {
                $existingEntry->kcal += $kcal;
                $existingEntry->carbs += $carbs;
                $existingEntry->fats += $fats;
                $existingEntry->proteins += $proteins;
                $existingEntry->portion += $foodPlannerData_I['quantity'];
                $existingEntry->no_of_servings += $foodPlannerData_I['no_of_servings'];
                $existingEntry->save();
            } else {
                $foodPlanner = new FoodPlanner;
                $foodPlanner->user_id = Auth::user()->id;
                $foodPlanner->recipe_or_ingredient_id = $foodPlannerData_I['recipe_or_ingredient_id'];
                $foodPlanner->meal_type = $data['meal_type'];
                $foodPlanner->date = $data['date'];
                $foodPlanner->portion = $foodPlannerData_I['quantity'];
                $foodPlanner->kcal = $kcal;
                $foodPlanner->carbs = $carbs;
                $foodPlanner->fats = $fats;
                $foodPlanner->proteins = $proteins;
                $foodPlanner->no_of_servings = $foodPlannerData_I['no_of_servings'];
                $foodPlanner->is_ingredient = $foodPlannerData_I['is_ingredient'];
                $foodPlanner->ai_Food_name = "";
                $foodPlanner->save();

                // If it's the first log for this meal_type, trigger the task logging function
                if ($firstLogged) {

                    $levelResponse =  $this->logDailyMealTypeTaskIfFirstTime(Auth::user()->id, $data['meal_type'], $data['date']);
                    if ($levelResponse != 0) {
                        $levelDetails = $this->getLevelDetails($levelResponse);
                    }
                    $firstLogged = false; // Prevent calling again for subsequent entries
                }
            }

            if($proteins > 0){

                $levelResponse = $this->logLevelTaskEntries(Auth::user()->id, 'protiens', $proteins, 0);
                if ($levelResponse != 0) {
                    $levelDetails = $this->getLevelDetails($levelResponse);
                }
            }
        }

        foreach ($filteredUserRecipes as $foodPlannerData_UR) {
            // Calorie Calculation from request

            $userRecipeData = UserRecipe::where(['id' => $foodPlannerData_UR['recipe_or_ingredient_id']])->first();

            if (isset($foodPlannerData_UR['quantity']) && !empty($foodPlannerData_UR['quantity'])) {
                if (!is_null($userRecipeData) && !empty($userRecipeData)) {
                    $kcal = ($foodPlannerData_UR['quantity'] * $userRecipeData['kcal']) / 100;
                    $carbs = ($foodPlannerData_UR['quantity'] * $userRecipeData['carbs']) / 100;
                    $fats = ($foodPlannerData_UR['quantity'] * $userRecipeData['fats']) / 100;
                    $proteins = ($foodPlannerData_UR['quantity'] * $userRecipeData['protein']) / 100;
                } else {
                    $kcal = 0;
                    $carbs = 0;
                    $fats = 0;
                    $proteins = 0;
                }
            }else{
                $kcal = 0;
                $carbs = 0;
                $fats = 0;
                $proteins = 0;
            }

            $existingEntry = FoodPlanner::where([
                'recipe_or_ingredient_id' => $foodPlannerData_UR['recipe_or_ingredient_id'],
                'is_ingredient' => $foodPlannerData_UR['is_ingredient'],
                'meal_type' => $data['meal_type'],
                'date' => $data['date'],
                'user_id' => Auth::user()->id
            ])->first();

            if ($existingEntry) {
                $existingEntry->kcal += $kcal;
                $existingEntry->carbs += $carbs;
                $existingEntry->fats += $fats;
                $existingEntry->proteins += $proteins;
                $existingEntry->portion += $foodPlannerData_UR['quantity'];
                $existingEntry->no_of_servings += $foodPlannerData_UR['no_of_servings'];
                $existingEntry->save();
            } else {
                $foodPlanner = new FoodPlanner;
                $foodPlanner->user_id = Auth::user()->id;
                $foodPlanner->recipe_or_ingredient_id = $foodPlannerData_UR['recipe_or_ingredient_id'];
                $foodPlanner->meal_type = $data['meal_type'];
                $foodPlanner->date = $data['date'];
                $foodPlanner->portion = $foodPlannerData_UR['quantity'];
                $foodPlanner->kcal = $kcal;
                $foodPlanner->carbs = $carbs;
                $foodPlanner->fats = $fats;
                $foodPlanner->proteins = $proteins;
                $foodPlanner->no_of_servings = $foodPlannerData_UR['no_of_servings'];
                $foodPlanner->is_ingredient = $foodPlannerData_UR['is_ingredient'];
                $foodPlanner->ai_Food_name ="";
                $foodPlanner->save();

                // If it's the first log for this meal_type, trigger the task logging function
                if ($firstLogged) {
                    $levelResponse =  $this->logDailyMealTypeTaskIfFirstTime(Auth::user()->id, $data['meal_type'], $data['date']);
                    if ($levelResponse != 0) {
                        $levelDetails = $this->getLevelDetails($levelResponse);
                    }
                    $firstLogged = false; // Prevent calling again for subsequent entries
                }
            }

            if ($proteins > 0) {
                $levelResponse = $this->logLevelTaskEntries(Auth::user()->id, 'protiens', $proteins, 0);
                if ($levelResponse != 0) {
                    $levelDetails = $this->getLevelDetails($levelResponse);
                }
            }
        }

        foreach ($filteredRecipes as $foodPlannerData_R) {
            $recipe = Recipe::where(['id' => $foodPlannerData_R['recipe_or_ingredient_id']])->first();

            if (isset($foodPlannerData_R['quantity']) && !empty($foodPlannerData_R['quantity'])) {
                $nutrition = RecipeNutrition::where(['recipe_id' => $foodPlannerData_R['recipe_or_ingredient_id'], 'id' => $foodPlannerData_R['nutrition_id']])->first();

                if (!is_null($nutrition) && !empty($nutrition)) {
                    $actualQuantity = RecipeNutrition::with(relations: 'nutritioningredients')
                        ->where('recipe_id', $foodPlannerData_R['recipe_or_ingredient_id'])
                        ->where('id', $foodPlannerData_R['nutrition_id'])
                        ->get()
                        ->flatMap(function ($nutrition) {
                            return $nutrition->nutritioningredients->map(fn($ingredient) => $ingredient->quantity);
                        })->sum();

                    $multiplyBy = round($foodPlannerData_R['quantity'] / $actualQuantity, 2);

                    $kcal = round($nutrition->kcal * $multiplyBy, 2);
                    $proteins = round($nutrition->protien * $multiplyBy, 2);
                    $fats = round($nutrition->fat * $multiplyBy, 2);
                    $carbs = round($nutrition->carbs * $multiplyBy, 2);
                } else {
                    $kcal = 0;
                    $carbs = 0;
                    $fats = 0;
                    $proteins = 0;
                }
            } else {
                $kcal = 0;
                $carbs = 0;
                $fats = 0;
                $proteins = 0;
            }

            $existingEntry = FoodPlanner::where([
                'recipe_or_ingredient_id' => $foodPlannerData_R['recipe_or_ingredient_id'],
                'is_ingredient' => $foodPlannerData_R['is_ingredient'],
                'meal_type' => $data['meal_type'],
                'date' => $data['date'],
                'user_id' => Auth::user()->id
            ])->first();

            if ($existingEntry) {
                $existingEntry->kcal += $kcal;
                $existingEntry->carbs += $carbs;
                $existingEntry->fats += $fats;
                $existingEntry->proteins += $proteins;
                $existingEntry->portion += $foodPlannerData_R['quantity'];
                $existingEntry->no_of_servings += $foodPlannerData_R['no_of_servings'];
                $existingEntry->save();
            } else {
                $foodPlanner = new FoodPlanner;
                $foodPlanner->user_id = Auth::user()->id;
                $foodPlanner->recipe_or_ingredient_id = $foodPlannerData_R['recipe_or_ingredient_id'];
                $foodPlanner->meal_type = $data['meal_type'];
                $foodPlanner->date = $data['date'];
                $foodPlanner->portion = $foodPlannerData_R['quantity'];
                $foodPlanner->kcal = $kcal;
                $foodPlanner->carbs = $carbs;
                $foodPlanner->fats = $fats;
                $foodPlanner->proteins = $proteins;
                $foodPlanner->no_of_servings = $foodPlannerData_R['no_of_servings'];
                $foodPlanner->is_ingredient = $foodPlannerData_R['is_ingredient'];
                $foodPlanner->ai_Food_name = "";
                $foodPlanner->save();

                // If it's the first log for this meal_type, trigger the task logging function
                if ($firstLogged) {
                    $levelResponse =  $this->logDailyMealTypeTaskIfFirstTime(Auth::user()->id, $data['meal_type'], $data['date']);
                    if ($levelResponse != 0) {
                        $levelDetails = $this->getLevelDetails($levelResponse);
                    }
                    $firstLogged = false; // Prevent calling again for subsequent entries
                }
            }

            if ($proteins > 0) {
                $levelResponse = $this->logLevelTaskEntries(Auth::user()->id, 'protiens', $proteins, 0);
                if ($levelResponse != 0) {
                    $levelDetails = $this->getLevelDetails($levelResponse);
                }
            }
        }

        $milestoneCompletedArray = [];

        $taskTypes = [
            'log-meals' => 'purple',
            'proteins'  => 'light-yellow',
        ];

        foreach ($taskTypes as $type => $color) {
            // Fetch all available milestones for current task type
            $milestones = TaskMilestone::where('milestone_type', $type)->get();

            // Get already completed milestone IDs for user for this task type
            $userCompletedMilestones = UserMilestone::where([
                'user_id' => Auth::user()->id,
                'milestone_type' => $type
            ])->pluck('milestone_id')->toArray();

            // Sum total completed count from UserLevelTaskLog
            $completedCount = UserLevelTaskLog::where([
                'user_id' => Auth::user()->id,
                'level_task_type' => $type
            ])->sum('completed_count');

            // Check which milestones have been newly achieved
            foreach ($milestones as $milestone) {
                if ($completedCount >= $milestone->milestone_count &&
                    !in_array($milestone->id, $userCompletedMilestones)
                ) {
                    $milestoneCompletedArray[] = [
                        'type' => $type,
                        'color' => $color,
                        'title' => $milestone->milestone_title,
                        'description' => $milestone->milestone_description,
                        'count' => $milestone->milestone_count,
                    ];

                    // Save to user_milestones table
                    UserMilestone::create([
                        'user_id' => Auth::user()->id,
                        'milestone_id' => $milestone->id,
                        'milestone_type' => $type,
                    ]);
                }
            }
        }
        return $this->getFoodPlannerData($data['date'], $milestoneCompletedArray, $levelDetails);
    }

    protected function getFoodPlannerData($date, $milestoneCompletedArray = [], $levelDetails = null)
    {
        if ($levelDetails === null) {
            $levelDetails = (object)[];
        }
        $foodplanner = FoodPlanner::where([
            'user_id' => Auth::user()->id,
            'date' => $date
        ])->get()->toArray();

        $foodplanner[] = ['milestoneCompletedArray' => $milestoneCompletedArray];
        $foodplanner[] = ['levelCompletedDetails' => $levelDetails];

        return $foodplanner;
    }
    public function getFoodPlanner(Request $request)
    {
        try {
            $validation = [
                'date' => ['required', 'date', 'date_format:Y-m-d'],
            ];
            $validator = Validator::make($request->all(), $validation);

            if ($validator->fails()) {
                return $this->validationError("Fail", $validator->errors()->first(), 200);
            }

            $data = $request->all();

            $allMealTypes = MealType::where('visible_in_tracker', 1)->get()->toArray();

            // Extract the 'slug' from each meal type and store them in a new array
            $mealTypes = array_map(function ($mealType) {
                return strtolower($mealType['slug']); // Convert to lowercase
            }, $allMealTypes);

            // Add 'water' to the end of the array
            $mealTypes[] = 'water';
            $response = [];

            // Initialize variables for calorie calculation
            $targetCalorie = 10000; // Assuming a target of 10000 calories
            $totalConsumedCalorie = 0;
            $totalConsumedCarbs = 0;
            $totalConsumedProteins = 0;
            $totalConsumedFats = 0;

            $totalCaloriesByType = []; // Array to store total calories by meal type
            $totalCarbsByType = []; // Array to store total carbs by meal type
            $totalProteinsByType = []; // Array to store total proteins by meal type
            $totalFatsByType = []; // Array to store total fats by meal type

            $bmrData = UsersInitialMeasurement::where('user_id', Auth::user()->id)->orderBy('id', 'desc')->first();
            $userMeasurementData = UsersCurrentMeasurement::with('initialMeasurement', 'targetMeasurement')
                ->where('user_id', Auth::user()->id)
                ->orderBy('id', 'desc')
                ->first();
            if ($userMeasurementData) {
                // If there's a record in UsersCurrentMeasurement, use it for weight
                $bmrData = $userMeasurementData;
            }
            if ($bmrData) {
                $userData = User::where('id', Auth::user()->id)->first();
                $weight = $bmrData['weight'];
                $initialMeasurement = UsersInitialMeasurement::where('user_id', Auth::user()->id)->latest('id')->first();
                $age = ($initialMeasurement && $initialMeasurement->age > 0) ? (int)$initialMeasurement->age : 24;
                if ($initialMeasurement && $initialMeasurement->created_at) {
                    $yearsElapsed = now()->diffInYears($initialMeasurement->created_at);
                    $age += $yearsElapsed;
                }
                $gender = strtolower($userData['gender']);
                $height = (!empty($userData['height']) && $userData['height'] != 0) ? $userData['height'] : 170;

                // Calculate BMR
                if ($gender == 'female') {
                    $bmr = (10 * $weight) + (6.25 * $height) - (5 * $age) - 161;
                } else {
                    $bmr = (10 * $weight) + (6.25 * $height) - (5 * $age) + 5;
                }

                $stepsGoal = StepsGoal::where(['user_id' => Auth::user()->id])->first();

                // Calculate TDEE
                $activityFactor = $stepsGoal['activity_factor'] ?? 1.237;
                $tdee = round(($bmr * $activityFactor));

                $targetCalorie = $tdee;
            }

            foreach ($mealTypes as $mealType) {
                // Initialize total calories for the current meal type
                $totalCaloriesByType[$mealType] = 0;
                $totalCarbsByType[$mealType] = 0;
                $totalProteinsByType[$mealType] = 0;
                $totalFatsByType[$mealType] = 0;

                $waterId = 0;
                $waterconsume = 0;
                $waterRemainingPortion = 10;
                $waterCompletedPortion = 0;
                // Fetch recipes or ingredients for each meal type
                $mealList = FoodPlanner::where(['user_id' => Auth::user()->id, 'meal_type' => $mealType, 'date' => $data['date']])->get()->toArray();
                $mealData = [];
                $mealTypeImage = MealType::where('slug', $mealType)->pluck('image')->first();
                $mealTypeID = MealType::where('slug', $mealType)->pluck('id')->first();
                if (empty($mealList)) {
                    $isLoggedToday = 0;
                }
                foreach ($mealList as $meal) {
                    if ($meal['date'] == date('Y-m-d')) {
                        $isLoggedToday = 1;
                    } else {
                        $isLoggedToday = 0;
                    }
                    if ($meal['is_ingredient'] == 1) {
                        $ingredientData = NutritionIngredient::where(['id' => $meal['recipe_or_ingredient_id']])->with(['units' => function ($query) { $query->select('nutrition_ingredient_id', 'size_key', 'value', 'units');}])->first();

                        $unitOptions = $ingredientData->units
                            ->map(function ($row) {
                                // Extract only the text part before the last underscore and number, if present
                                if (preg_match('/^(.+?)_\d+g$/', $row->size_key, $matches)) {
                                    $displayKey = $matches[1];
                                } else {
                                    $displayKey = $row->size_key;
                                }

                                // Assign the display value to the 'display' attribute
                                $row->display = $displayKey . ' - ' . $row->value;
                                return $row;
                            });

                        if (!empty($ingredientData)) {

                            $isUserCreated = 0;
                            if($ingredientData->user_id != null){
                                $isUserCreated = 1;
                            }
                            $mealData[] = [
                                "id" => $meal['id'],
                                "recipe_or_ingredient_id" => $meal['recipe_or_ingredient_id'],
                                "title" => $ingredientData['name'],
                                "meal_type" => $meal['meal_type'],
                                "date" => $meal['date'],
                                "kcal" => $meal['kcal'],
                                "carbs" => $meal['carbs'],
                                "proteins" => $meal['proteins'],
                                "fats" => $meal['fats'],
                                "quantity" => $meal['portion'],
                                "no_of_servings" => $meal['no_of_servings'],
                                "image" => $ingredientData['image'] ? asset(Storage::url($ingredientData['image'])): '',
                                "is_ingredient" => $meal['is_ingredient'],
                                "units" => $unitOptions,
                                'isUserCreated' => $isUserCreated,
                            ];

                            $totalCaloriesByType[$mealType] += (int)round($meal['kcal']); // Accumulate total consumed calories for this meal type
                            $totalConsumedCalorie += (int)$meal['kcal']; // Accumulate total consumed calories overall
                            $totalConsumedCarbs += (int)round($meal['carbs']); // Accumulate total consumed carbs overall
                            $totalConsumedProteins += (int)round($meal['proteins']); // Accumulate total consumed proteins overall
                            $totalConsumedFats += (int)round($meal['fats']); // Accumulate total consumed fats overall
                        }
                    }
                    if ($meal['is_ingredient'] == 3) {
                        $userRecipeData = UserRecipe::where(['id' => $meal['recipe_or_ingredient_id']])->first();

                        if (!empty($userRecipeData)) {
                            $mealData[] = [
                                "id" => $meal['id'],
                                "recipe_or_ingredient_id" => $meal['recipe_or_ingredient_id'],
                                "title" => $userRecipeData['title'],
                                "meal_type" => $meal['meal_type'],
                                "date" => $meal['date'],
                                "kcal" => $meal['kcal'],
                                "carbs" => $meal['carbs'],
                                "proteins" => $meal['proteins'],
                                "fats" => $meal['fats'],
                                "quantity" => $meal['portion'],
                                "no_of_servings" => $meal['no_of_servings'],
                                "image" => $userRecipeData['image'] ? asset(Storage::url($userRecipeData['image'])) : '',
                                "is_ingredient" => $meal['is_ingredient'],
                                "units" => []
                            ];

                            $totalCaloriesByType[$mealType] += (int)round($meal['kcal']); // Accumulate total consumed calories for this meal type
                            $totalConsumedCalorie += (int)$meal['kcal']; // Accumulate total consumed calories overall
                            $totalConsumedCarbs += (int)round($meal['carbs']); // Accumulate total consumed carbs overall
                            $totalConsumedProteins += (int)round($meal['proteins']); // Accumulate total consumed proteins overall
                            $totalConsumedFats += (int)round($meal['fats']); // Accumulate total consumed fats overall
                        }
                    }
                    if ($meal['is_ingredient'] == 0) {

                        $recipeData = Recipe::where(['id' => $meal['recipe_or_ingredient_id']])->first();

                        if (!empty($recipeData)) {
                            $mealData[] = [
                                "id" => $meal['id'],
                                "recipe_or_ingredient_id" => $meal['recipe_or_ingredient_id'],
                                "title" => $recipeData['title'],
                                "meal_type" => $meal['meal_type'],
                                "date" => $meal['date'],
                                "kcal" => $meal['kcal'],
                                "carbs" => $meal['carbs'],
                                "proteins" => $meal['proteins'],
                                "fats" => $meal['fats'],
                                "quantity" => $meal['portion'],
                                "no_of_servings" => $meal['no_of_servings'],
                                "image" => $recipeData['picture'] ? asset(Storage::url($recipeData['picture'])) : '',
                                "is_ingredient" => $meal['is_ingredient'],
                                "units" => []
                            ];

                            $totalCaloriesByType[$mealType] += (int)round($meal['kcal']); // Accumulate total consumed calories for this meal type
                            $totalConsumedCalorie += (int)$meal['kcal']; // Accumulate total consumed calories overall
                            $totalConsumedCarbs += (int)round($meal['carbs']); // Accumulate total consumed carbs overall
                            $totalConsumedProteins += (int)round($meal['proteins']); // Accumulate total consumed proteins overall
                            $totalConsumedFats += (int)round($meal['fats']); // Accumulate total consumed fats overall
                        }
                    }
                    if ($meal['is_ingredient'] == 4) {


                            $mealData[] = [
                                "id" => $meal['id'],
                                "recipe_or_ingredient_id" => $meal['recipe_or_ingredient_id'],
                                "title" =>$meal['AI_food_name'],
                                "meal_type" => $meal['meal_type'],
                                "date" => $meal['date'],
                                "kcal" => $meal['kcal'],
                                "carbs" => $meal['carbs'],
                                "proteins" => $meal['proteins'],
                                "fats" => $meal['fats'],
                                "quantity" => $meal['portion'],
                                "no_of_servings" => $meal['no_of_servings'],
                                "image" =>  '',
                                "is_ingredient" => $meal['is_ingredient'],
                                "units" => []
                            ];

                            $totalCaloriesByType[$mealType] += (int)round($meal['kcal']); // Accumulate total consumed calories for this meal type
                            $totalConsumedCalorie += (int)$meal['kcal']; // Accumulate total consumed calories overall
                            $totalConsumedCarbs += (int)round($meal['carbs']); // Accumulate total consumed carbs overall
                            $totalConsumedProteins += (int)round($meal['proteins']); // Accumulate total consumed proteins overall
                            $totalConsumedFats += (int)round($meal['fats']); // Accumulate total consumed fats overall
                    }
                }

                if ($mealType == 'water') {
                    if ($mealList != []) {
                        $waterconsume = $mealList[0]['water_consume'];
                        $waterId = $mealList[0]['id'];
                        $waterCompletedPortion = $waterconsume / 250;
                        $waterRemainingPortion = 10 - $waterCompletedPortion;
                    }
                    $mealRecipe = [
                        "id" => $waterId,
                        "name" => ucfirst($mealType),
                        "water_consume" => $waterconsume,
                        "water_completed_portion" => $waterCompletedPortion,
                        "water_remaining_portion" => $waterRemainingPortion,
                    ];
                } else {
                    // Store meal type and its recipes
                    $mealRecipe = [
                        "id" => $mealTypeID,
                        "name" => ucfirst($mealType),
                        "image" => $mealTypeImage ? asset(Storage::url($mealTypeImage)) : '',
                        "totalCalories" => $totalCaloriesByType[$mealType],
                        "isLoggedToday" => $isLoggedToday,
                        "recipe" => $mealData
                    ];
                }
                $response[] = $mealRecipe;
            }

            // Calculate remaining calories
            $remainingCalorie = $targetCalorie - $totalConsumedCalorie;
            // $remainingCalorie = max($remainingCalorie, 0);

            // Calorie And Macro
            // Calorie
            foreach ($mealTypes as $mealType) {
                if ($mealType == "water") {
                    continue; // Skip this iteration if mealType is "water"
                }
                $mealWiseCalorie[$mealType] = $totalCaloriesByType[$mealType];
            }
            $goal = $targetCalorie;

            foreach ($mealWiseCalorie as $key => $value) {
                $consumedCalorie = $value;
                $percent = round((100 * $consumedCalorie) / $goal);
                if ($percent > 100) {
                    $percent = 100;
                }
                $calorie[] = [
                    "name" => $key,
                    "percent" => $percent . "%",
                    "consumedCalorie" => $consumedCalorie
                ];
            }

            $calorieDetails = new stdClass();
            foreach ($calorie as $index => $meal) {
                $calorieDetails->{$index} = $meal;
            }

            $calorie = [
                "name" => "calorie",
                "details" => $calorieDetails,
                "netCalorie" => $totalConsumedCalorie,
                "goal" => $targetCalorie
            ];
            $response[] = $calorie;

            // New Macro
            $targetCarbsCalories = $goal * 0.4;
            $targetCarbsGrams = round($targetCarbsCalories / 4);

            $targetProteinsCalories = $goal * 0.3;
            $targetProteinsGrams = round($targetProteinsCalories / 4);

            $targetFatsCalories = $goal * 0.3;
            $targetFatsGrams = round($targetFatsCalories / 9);

            $carbsPercentage = round(($totalConsumedCarbs / $targetCarbsGrams) * 100);
            $proteinsPercentage = round(($totalConsumedProteins / $targetProteinsGrams) * 100);
            $fatsPercentage = round(($totalConsumedFats / $targetFatsGrams) * 100);

            $carbsPercentage = min($carbsPercentage, 100);
            $proteinsPercentage = min($proteinsPercentage, 100);
            $fatsPercentage = min($fatsPercentage, 100);
            // End

            // Prepare the macro array with the target and percentage consumed
            $macro = [
                'name' => "macro",
                'carbs' => [
                    'target' => $targetCarbsGrams,
                    'consumed' => $totalConsumedCarbs,
                    'percentage' => $carbsPercentage . '%'
                ],

                'proteins' => [
                    'target' => $targetProteinsGrams,
                    'consumed' => $totalConsumedProteins,
                    'percentage' => $proteinsPercentage . '%'
                ],

                'fats' => [
                    'target' => $targetFatsGrams,
                    'consumed' => $totalConsumedFats,
                    'percentage' => $fatsPercentage . '%'
                ]
            ];

            $response[] = $macro;

            // Add calorie details to the response
            $calorieDetails = [
                "name" => "calorieDetails",
                "targetCalorie" => $targetCalorie,
                "mealWiseCalorie" => $mealWiseCalorie,
                "totalConsumedCalorie" => $totalConsumedCalorie,
                "remainingCalorie" => $remainingCalorie
            ];

            $response[] = $calorieDetails;

            $foodPlanner = FoodPlanner::where(['user_id' => Auth::user()->id])->latest()->first();
            $isLogForFood = 0;

            if ($foodPlanner) {
                $isLogForFood = 1;
            }
            $isFoodLoggedEver = [
                "name" => "foodLogged",
                'isFoodLoggedEver' => $isLogForFood
            ];
            $response[] = $isFoodLoggedEver;

            return $this->successResponse("Data Found", $response, 200);
        } catch (\Throwable $th) {
            return $this->throwableError("Something went wrong", $th->getMessage(), 500);
        }
    }

    public function deleteFoodPlanner(Request $request)
    {
        try {
            /* Validate Data */
            $validation = [
                'id' => ['required', 'integer'],
                'date' => ['required', 'date', 'date_format:Y-m-d'],
            ];
            $validator = Validator::make($request->all(), $validation);

            if ($validator->fails()) {
                return $this->validationError("Fail", $validator->errors()->first(), 200);
            }
            $getLoggedFood = FoodPlanner::where(['id' => $request->id, 'user_id' => Auth::user()->id, 'date' => $request->date])->first();

            if ($getLoggedFood != null) {
                if ($getLoggedFood['is_ingredient'] == 2 && $getLoggedFood['water_consume'] > 250) {
                    $waterConsumed = $getLoggedFood['water_consume'] - 250;
                    $getLoggedFood->update(['water_consume' => $waterConsumed]);
                    $waterCompletedPortion = $getLoggedFood['water_consume'] / 250;
                    $waterRemainingPortion = 10 - $waterCompletedPortion;
                    $mealRecipe = [
                        "id" => $getLoggedFood['id'],
                        "name" => ucfirst($request->meal_type),
                        "water_consume" => $getLoggedFood['water_consume'],
                        "water_completed_portion" => $waterCompletedPortion,
                        "water_remaining_portion" => $waterRemainingPortion,
                    ];
                    $this->deleteLogWaterEntries(Auth::User()->id);
                    return $this->successResponse("Water Consumption Updated", $mealRecipe, 200);
                }
                if ($getLoggedFood['is_ingredient'] == 2) {
                    $this->deleteLogWaterEntries(Auth::User()->id);
                } else {
                    $this->deleteLogProtienEntries(Auth::user()->id, $getLoggedFood['proteins']);

                    $this->deleteLogMealTypeEntries(Auth::user()->id, $getLoggedFood['meal_type'], $getLoggedFood['date']);
                }
                $getLoggedFood->delete();
                $mealList = FoodPlanner::where(['user_id' => Auth::user()->id, 'meal_type' => $request->meal_type, 'date' => $request->date])->get()->toArray();
                $mealData = [];
                foreach ($mealList as $meal) {
                    if ($meal['is_ingredient'] == 0) {
                        $recipeData = Recipe::with('nutrition')->where(['id' => $meal['recipe_or_ingredient_id']])->first();
                        if (!empty($recipeData)) {
                            $nutritionDetails = $recipeData->nutrition()->orderBy('kcal', 'asc')->first();

                            $mealData[] = [
                                "id" => $meal['id'],
                                "recipe_or_ingredient_id" => $meal['recipe_or_ingredient_id'],
                                "title" => $recipeData['title'],
                                "meal_type" => $meal['meal_type'],
                                "date" => $meal['date'],
                                "kcal" => $meal['kcal'],
                                "carbs" => $nutritionDetails['carbs'],
                                "proteins" => $nutritionDetails['protien'],
                                "fats" => $nutritionDetails['fats'],
                                "no_of_servings" => $meal['no_of_servings'],
                                "image" => asset(Storage::url($recipeData['picture'])),
                                "is_ingredient" => $meal['is_ingredient'],
                                "units" => []
                            ];
                        }
                    } else if ($meal['is_ingredient'] == 1) {
                        $ingredientData = NutritionIngredient::where(['id' => $meal['recipe_or_ingredient_id']])->with(['units' => function ($query) { $query->select('nutrition_ingredient_id', 'size_key', 'value', 'units');}])->first();

                        $unitOptions = $ingredientData->units
                            ->map(function ($row) {
                                // Extract only the text part before the last underscore and number, if present
                                if (preg_match('/^(.+?)_\d+g$/', $row->size_key, $matches)) {
                                    $displayKey = $matches[1];
                                } else {
                                    $displayKey = $row->size_key;
                                }

                                // Assign the display value to the 'display' attribute
                                $row->display = $displayKey . ' - ' . $row->value;
                                return $row;
                            });

                        if (!empty($ingredientData)) {

                            $isUserCreated = 0;
                            if($ingredientData->user_id != null){
                                $isUserCreated = 1;
                            }
                            $mealData[] = [
                                "id" => $meal['id'],
                                "recipe_or_ingredient_id" => $meal['recipe_or_ingredient_id'],
                                "title" => $ingredientData['name'],
                                "meal_type" => $meal['meal_type'],
                                "date" => $meal['date'],
                                "kcal" => $meal['kcal'],
                                "carbs" => $meal['carbs'],
                                "proteins" => $meal['proteins'],
                                "fats" => $meal['fats'],
                                "no_of_servings" => $meal['no_of_servings'],
                                "image" => asset(Storage::url($ingredientData['image'])),
                                "is_ingredient" => $meal['is_ingredient'],
                                "units" => $unitOptions,
                                'isUserCreated' => $isUserCreated,
                            ];
                        }
                    } else if ($meal['is_ingredient'] == 0) {
                        $recipe = Recipe::where(['id' => $meal['recipe_or_ingredient_id']])->first();
                        if (!empty($recipe)) {
                            $mealData[] = [
                                "id" => $meal['id'],
                                "recipe_or_ingredient_id" => $meal['recipe_or_ingredient_id'],
                                "title" => $recipe['name'],
                                "meal_type" => $meal['meal_type'],
                                "date" => $meal['date'],
                                "kcal" => $meal['kcal'],
                                "carbs" => $meal['carbs'],
                                "proteins" => $meal['protein'],
                                "fats" => $meal['fat'],
                                "no_of_servings" => $meal['no_of_servings'],
                                "image" => $recipe['picture'] ? asset(Storage::url($recipe['picture'])) : '',
                                "is_ingredient" => $meal['is_ingredient'],
                                "units" => []
                            ];
                        }
                    } else if ($meal['is_ingredient'] == 4) {
                            $mealData[] = [
                                "id" => $meal['id'],
                                "recipe_or_ingredient_id" => $meal['recipe_or_ingredient_id']??0,
                                "title" => $meal['AI_food_name'],
                                "meal_type" => $meal['meal_type'],
                                "date" => $meal['date'],
                                "kcal" => $meal['kcal'],
                                "carbs" => $meal['carbs'],
                                "proteins" => $meal['proteins'],
                                "fats" => $meal['fats'],
                                "no_of_servings" => $meal['no_of_servings'],
                                "image" => '',
                                "is_ingredient" => $meal['is_ingredient'],
                                "units" => []
                            ];
                    }
                }

                $mealRecipe = [
                    "name" => ucfirst($request->meal_type),
                    "recipe" => $mealData
                ];
                return $this->successResponse("Item deleted", $mealRecipe, 200);
            }
            return $this->successResponse("Data Not Found.", [], 200);
        } catch (\Throwable $th) {
            return $this->throwableError("Something went wrong", $th->getMessage(), 500);
        }
    }


    public function fetchFoodJsonFromAI(Request $request)
    {
        $data = $request->get('food');

        if (empty($data)) {
            return $this->successResponse("No data provided", ['ingredients' => []], 200);
        }

        $client = OpenAI::client(env('OPENAI_API_KEY'));

        // Initial AI prompt
            $prompt = <<<EOT
You are a nutrition assistant. Return a pure JSON array based on the following input sentence in French or English.

[input sentence]: {$data}

TASK:
    1. Extract all food items from the sentence (ignore non-food words).

    2. For each food item:
        - Extract portion/quantity if provided (e.g., "2 bananas", "10g paneer", "a can of coke").
	- If the portion is provided put it as the top 1 in the list of the 5 most common portions sizes
        - If no portion is given, search and provide the 5 most common portion sizes for that specific food item.
	- For the portion make sure it's common french portion name (e.g : chicken : breast), it needs to be understandable by the user
	- Try to make sure the portions are different between each other, and provide the most classic portion in first (e.g : banana : medium size banana)

    3. Return the following JSON object for each food:
        - Nutrition data per 100g/ml: "kcal", "protein", "carbs", "fats"
        - Field "quantity" should always be "100g" or "100ml" based on food type
        - "value" and "quantity" in the units array must represent numeric strings with two decimal places
        - kcal, protein, carbs, fats — all must be strings with two decimals

FORMAT RULES:

    - Return only the JSON array: [ {...}, {...} ]
    - Do not include any text, markdown, code fences, or explanation
    - All numerical fields (kcal, protein, carbs, fats, value, quantity) must be strings with two decimal places, e.g. "42.00"
    - No variables or placeholders like xyz * 2 — use real calculated values
    - Ensure the response is valid JSON, parsable by JSON.parse()

Example input sentence:
    - J'ai mangé 2 bananes, une salade César, 10 grammes de paneer, une canette de coca.

Example response:
    [
        {
            "name": "banane",
            "kcal": "89.00",
            "protein": "1.10",
            "carbs": "22.80",
            "fats": "0.30",
            "quantity": "100g",
            "is_ingredient": 4,
            "recipe_or_ingredient_id": 0,
            "image_url": "",
            "small_image_url": "",
            "units": [
            {
                "size_key": "banane_moyenne",
                "value": "2.00",
                "quantity": "240.00",
                "units": "gram",
                "display": "2 bananes moyennes",
                "kcal": "213.60",
                "protein": "2.64",
                "carbs": "54.72",
                "fats": "0.72"
            },
            ...
            ],
            "barcodes": [],
            "is_favourite": 0
        },
        ....
    ]

ADDITIONAL RULES:
    - All food names ("name") and unit names ("units", "display") must be in French only.
    - Maintain correct singular/plural forms in French.
    - Units must be unique within the list for each food item (no duplicate unit names).
    - Each food item must always contain exactly 5 unique unit variations in "units".
EOT;
        try {
            $result = $client->chat()->create([
                'model' => 'gpt-5',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a French nutrition JSON extractor. Return only JSON. All numbers as strings with two decimals.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                // new parameters supported by GPT-5
                'verbosity' => 'low',             // ask it to produce concise outputs
                'reasoning_effort' => 'minimal', // reduce internal reasoning overhead
            ]);

            $responseText = $result->choices[0]->message->content;

            $aiData = [];
            $aiData = json_decode($responseText, true);

            if (empty($aiData)) {
                Log::info('AI raw response', ['responseText' => $responseText]);
                return $this->errorResponse("Something went wrong, please re-submit your input.", [], 200);
            }

            return $this->successResponse("Nutrition Found", ['ingredients' => $aiData], 200);
        } catch (\OpenAI\Exceptions\ErrorException $e) {
            // Handle quota exceeded or other OpenAI errors
            if (str_contains($e->getMessage(), 'You exceeded your current quota')) {
                Log::warning('OpenAI quota exceeded', ['error' => $e->getMessage()]);
                return $this->errorResponse(
                    "Quota exceeded. Please check your OpenAI billing or try again later.",
                    [],
                    429
                );
            }

            Log::error('OpenAI API error', ['error' => $e->getMessage()]);
            return $this->errorResponse(
                "An error occurred while fetching data from AI. Please try again later. : {$e->getMessage()} ",
                [],
                500
            );
        } catch (\Exception $e) {
            // Catch any other unexpected errors
            Log::error('Unexpected error', ['error' => $e->getMessage()]);
            return $this->errorResponse(
                "An unexpected error occurred. Please try again later.: {$e->getMessage()}",
                [],
                500
            );
        }
    }
    public function updateDailyFoodPlanner(Request $request){
        try {
            /* Validate Data */
            $validation = [
                'id' => ['required', 'integer'],
                'date' => ['required', 'date', 'date_format:Y-m-d'],
                'quantity' => ['required', 'numeric'],
            ];
            $validator = Validator::make($request->all(), $validation);

            if ($validator->fails()) {
                return $this->validationError("Fail", $validator->errors()->first(), 200);
            }
            $getLoggedFood = FoodPlanner::where(['id' => $request->id, 'user_id' => Auth::user()->id, 'date' => $request->date])->first();

            if ($getLoggedFood != null) {

                // Step 1: Store old protein value
                $oldQuantity = $getLoggedFood->portion > 0 ? $getLoggedFood->portion : 1; // avoid division by zero
                $perGramProteins = $getLoggedFood->proteins / $oldQuantity;
                $oldProteins = $getLoggedFood->proteins;

                // Step 2: Calculate new values
                $newQuantity = $request->quantity;
                $newProteins = round($perGramProteins * $newQuantity, 2);

                $getLoggedFood->portion = $newQuantity;
                $getLoggedFood->kcal = round(($getLoggedFood->kcal / $oldQuantity) * $newQuantity, 2);
                $getLoggedFood->carbs = round(($getLoggedFood->carbs / $oldQuantity) * $newQuantity, 2);
                $getLoggedFood->proteins = $newProteins;
                $getLoggedFood->fats = round(($getLoggedFood->fats / $oldQuantity) * $newQuantity, 2);

                $getLoggedFood->save();

                // Step 3: Adjust protein logs
                $proteinDiff = round($newProteins - $oldProteins, 2);

                if ($proteinDiff > 0) {
                    // Add protein log
                    UserLevelTaskLog::create([
                        'user_id' => Auth::user()->id,
                        'level_task_type' => 'protiens',
                        'completed_count' => $proteinDiff,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } elseif ($proteinDiff < 0) {
                    // Remove protein logs
                    $this->deleteLogProtienEntries(Auth::user()->id, abs($proteinDiff));
                }

                $mealList = FoodPlanner::where(['user_id' => Auth::user()->id, 'meal_type' => $request->meal_type, 'date' => $request->date])->get()->toArray();
                $mealData = [];
                foreach ($mealList as $meal) {
                    if ($meal['is_ingredient'] == 0) {
                        $recipeData = Recipe::with('nutrition')->where(['id' => $meal['recipe_or_ingredient_id']])->first();
                        if (!empty($recipeData)) {
                            $nutritionDetails = $recipeData->nutrition()->orderBy('kcal', 'asc')->first();

                            $mealData[] = [
                                "id" => $meal['id'],
                                "recipe_or_ingredient_id" => $meal['recipe_or_ingredient_id'],
                                "title" => $recipeData['title'],
                                "meal_type" => $meal['meal_type'],
                                "date" => $meal['date'],
                                "kcal" => $meal['kcal'],
                                "carbs" => $nutritionDetails['carbs'],
                                "proteins" => $nutritionDetails['protien'],
                                "fats" => $nutritionDetails['fats'],
                                "no_of_servings" => $meal['no_of_servings'],
                                "image" => asset(Storage::url($recipeData['picture'])),
                                "is_ingredient" => $meal['is_ingredient'],
                                "units" => []
                            ];
                        }
                    } else if ($meal['is_ingredient'] == 1) {
                        $ingredientData = NutritionIngredient::where(['id' => $meal['recipe_or_ingredient_id']])->with(['units' => function ($query) { $query->select('nutrition_ingredient_id', 'size_key', 'value', 'units');}])->first();

                        $unitOptions = $ingredientData->units
                            ->map(function ($row) {
                                // Extract only the text part before the last underscore and number, if present
                                if (preg_match('/^(.+?)_\d+g$/', $row->size_key, $matches)) {
                                    $displayKey = $matches[1];
                                } else {
                                    $displayKey = $row->size_key;
                                }

                                // Assign the display value to the 'display' attribute
                                $row->display = $displayKey . ' - ' . $row->value;
                                return $row;
                            });
                        if (!empty($ingredientData)) {
                            $isUserCreated = 0;
                            if($ingredientData->user_id != null){
                                $isUserCreated = 1;
                            }
                            $mealData[] = [
                                "id" => $meal['id'],
                                "recipe_or_ingredient_id" => $meal['recipe_or_ingredient_id'],
                                "title" => $ingredientData['name'],
                                "meal_type" => $meal['meal_type'],
                                "date" => $meal['date'],
                                "kcal" => $meal['kcal'],
                                "carbs" => $meal['carbs'],
                                "proteins" => $meal['proteins'],
                                "fats" => $meal['fats'],
                                "no_of_servings" => $meal['no_of_servings'],
                                "image" => asset(Storage::url($ingredientData['image'])),
                                "is_ingredient" => $meal['is_ingredient'],
                                "units" => $unitOptions,
                                'isUserCreated' => $isUserCreated,
                            ];
                        }
                    } else if ($meal['is_ingredient'] == 0) {
                        $recipe = Recipe::where(['id' => $meal['recipe_or_ingredient_id']])->first();
                        if (!empty($recipe)) {
                            $mealData[] = [
                                "id" => $meal['id'],
                                "recipe_or_ingredient_id" => $meal['recipe_or_ingredient_id'],
                                "title" => $recipe['name'],
                                "meal_type" => $meal['meal_type'],
                                "date" => $meal['date'],
                                "kcal" => $meal['kcal'],
                                "carbs" => $meal['carbs'],
                                "proteins" => $meal['protein'],
                                "fats" => $meal['fat'],
                                "no_of_servings" => $meal['no_of_servings'],
                                "image" => $recipe['picture'] ? asset(Storage::url($recipe['picture'])) : '',
                                "is_ingredient" => $meal['is_ingredient'],
                                "units" => []
                            ];
                        }
                    } else if ($meal['is_ingredient'] == 4) {
                            $mealData[] = [
                                "id" => $meal['id'],
                                "recipe_or_ingredient_id" => $meal['recipe_or_ingredient_id']??0,
                                "title" => $meal['AI_food_name'],
                                "meal_type" => $meal['meal_type'],
                                "date" => $meal['date'],
                                "kcal" => $meal['kcal'],
                                "carbs" => $meal['carbs'],
                                "proteins" => $meal['proteins'],
                                "fats" => $meal['fats'],
                                "no_of_servings" => $meal['no_of_servings'],
                                "image" => '',
                                "is_ingredient" => $meal['is_ingredient'],
                                "units" => []
                            ];
                    }
                }

                $mealRecipe = [
                    "name" => ucfirst($request->meal_type),
                    "recipe" => $mealData
                ];
                return $this->successResponse("Food Data updated successfully!", $mealRecipe, 200);
            }
            return $this->successResponse("Data Not Found.", [], 200);
        } catch (\Throwable $th) {
            return $this->throwableError("Something went wrong", $th->getMessage(), 500);
        }
    }

}

