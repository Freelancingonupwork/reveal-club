<?php

namespace App\Http\Controllers\API\V3;

use App\Http\Controllers\Controller;
use App\Models\FoodPlanner;
use App\Models\MealType;
use App\Models\NutritionIngredient;
use App\Models\Recipe;
use App\Models\StepsGoal;
use App\Models\TrackedActivity;
use App\Models\User;
use App\Models\UserRecipe;
use App\Models\UsersCurrentMeasurement;
use App\Models\UsersInitialMeasurement;
use Illuminate\Http\Request;

use App\Http\Controllers\API\V2\FoodPlanerController as APIFoodPlanerControllerV2;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use stdClass;

class FoodPlanerController extends APIFoodPlanerControllerV2
{
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

            $totalBurnedCalorie = TrackedActivity::where('user_id', Auth::user()->id)
                ->where('activity_date', $data['date'])
                ->sum('calories_burned');

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
            $remainingCalorie = ($targetCalorie + $totalBurnedCalorie) - $totalConsumedCalorie;
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

            $totalBurnedCalorie = number_format((float) $totalBurnedCalorie, 2);

            // Add calorie details to the response
            $calorieDetails = [
                "name" => "calorieDetails",
                "targetCalorie" => $targetCalorie,
                "mealWiseCalorie" => $mealWiseCalorie,
                "totalConsumedCalorie" => $totalConsumedCalorie,
                "totalBurnedCalorie" => $totalBurnedCalorie,
                "remainingCalorie" => number_format((float) $remainingCalorie,2)
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
}
