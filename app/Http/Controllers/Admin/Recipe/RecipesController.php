<?php

namespace App\Http\Controllers\Admin\Recipe;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\IngredientCategory;
use App\Models\MealType;
use App\Models\Nutrition;
use App\Models\RecepieIngredients;
use App\Models\Recipe;
use App\Models\RecipeMaterial;
use App\Models\RecipeNutrition;
use App\Models\RecipePreparationSteps;
use App\Models\ToAccompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RecipesController extends Controller
{
    public function index()
    {
        Session::put('page', 'recepies');
        $recipesData = Recipe::get()->toArray();
        // dd($recipesData);
        return view('admin.recipeDiet.recipe.index', ['recipesData' => $recipesData]);
    }

    public function create(Request $request)
    {
        Session::put('page', 'recepies');
        $mealTypeData = MealType::get()->toArray();
        $ingredientCategory = IngredientCategory::get()->toArray();
        $ingredientList = Ingredient::get()->toArray();
        if ($request->isMethod('post')) {
            $data = $request->all();
            // echo "<pre>"; print_r($data); die;
            $rules = [
                'title' => ['required', 'string', 'max:255'],
                'picture' => ['required', 'image', 'mimes:jpg,jpeg,png'],
                'cooking_time' => ['required'],
                'overall_time' => ['required'],
                'about' => ['required'],
                'tags' => ['required'],
                'meal_type' => ['required'],
                'preparation' => ['required'],
                // 'ingredients' => ['required'],
            ];

            $message = [
                'title.required' => "Title is required.",
                'picture.required' => "Recipe image is required.",
                'picture.mimes' => "Please select image with these file format jpg,jpeg,png.",
                'cooking_time.required' => "Please enter approx time it will take to cook this recipe.",
                'overall_time.required' => "Please enter overall time it will take to cook this recipe.",
                'about.required' => "Write in some words about this recipe.",
                'tags.required' => "Please enter atleast one tag",
                'meal_type.required' => "Please select atleast one meal type.",
                'preparation.required' => "Please write steps to prepare this recipe.",
                // 'ingredients.required' => "Please list ingredients required to prepare this recipe.",
            ];
            $validator = Validator::make($data, $rules, $message);
            $input = $request->except(['_token']);
            if ($validator->fails()) {
                return redirect()->back()->withInput()->withErrors($validator->getMessageBag());
            }

            if (empty($data['status'])) {
                $status = 0;
            } else {
                $status = 1;
            }

            if (empty($data['free_access'])) {
                $free_access = 0;
            } else {
                $free_access = 1;
            }

            if (empty($data['special_recipe'])) {
                $special_recipe = 0;
            } else {
                $special_recipe = 1;
            }

            if (empty($data['is_person'])) {
                $person = 0;
                $noOfPerson = 1;
            } else {
                $person = 1;
                $noOfPerson = $data['no_of_persons'];
            }

            if (!isset($data['prep_video']) || empty($data['prep_video'])) {
                $data['prep_video'] = "";
            }

            $slug = Str::slug($data['title']);

            // Recipe Image
            if ($request->has('picture')) {
                $recipeImage = time() . '.' . $data['picture']->extension();
                if (!Storage::disk('public')->exists("/recipes/image")) {
                    Storage::disk('public')->makeDirectory("/recipes/image"); //creates directory
                }

                $request->picture->storeAs("recipes/image", $recipeImage, 'public');
                $data['picture'] = "recipes/image/$recipeImage";
            }

            $recipe = new Recipe;
            $recipe->title = $data['title'];
            $recipe->tags = implode("|", $data['tags']);
            $recipe->meal_type_id = implode("|", $data['meal_type']);
            $recipe->cooking_time = $data['cooking_time'];
            $recipe->overall_time = $data['overall_time'];
            $recipe->about = $data['about'];
            $recipe->prep_video = $data['prep_video'];
            $recipe->picture = $data['picture'];
            $recipe->slug = $slug;
            $recipe->is_person = $person;
            $recipe->no_of_person = $noOfPerson;
            $recipe->status = $status;
            $recipe->free_access = $free_access;
            $recipe->special_recipe = $special_recipe;
            $recipe->save();

            foreach ($data['preparation'] as $preparation) {
                $preparationStep = new RecipePreparationSteps;
                $preparationStep->recipe_id = $recipe->id;
                $preparationStep->prep_steps = $preparation['stepTitle'];
                $preparationStep->prep_description = $preparation['stepDescription'];
                $preparationStep->save();
            }

            foreach ($data['ingredientsWithNutrition'] as $recipeNutrition) {
                $nutrition = new RecipeNutrition;
                $nutrition->recipe_id = $recipe->id;
                $nutrition->kcal = $recipeNutrition['kcal'];
                $nutrition->protien = $recipeNutrition['protein'];
                $nutrition->fat = $recipeNutrition['fat'];
                $nutrition->carbs = $recipeNutrition['carbs'];
                $nutrition->save();

                foreach ($ingredientList as $commonIngrdient) {
                    foreach ($recipeNutrition['ingredients'] as $key => $ingredient) {
                        if ($commonIngrdient['id'] == $ingredient['ingredient_id']) {

                            $recipeIngredient = new RecepieIngredients;
                            $recipeIngredient['recepie_id'] = $recipe->id;
                            $recipeIngredient['ingredient_id'] = $ingredient['ingredient_id'];
                            $recipeIngredient['nutrition_id'] = $nutrition['id'];
                            $recipeIngredient['name'] = $commonIngrdient['name'];
                            $recipeIngredient['quantity'] = $ingredient['quantity'];
                            $recipeIngredient['unit'] = $ingredient['unit'];
                            $recipeIngredient['category_id'] = $ingredient['category_id'];
                            $recipeIngredient->save();
                        }
                    }
                }
            }

            foreach ($data['accompany'] as $toAccompany) {

                $accompany = new ToAccompany;
                $accompany->recipe_id = $recipe->id;
                $accompany->ingredient_name = $toAccompany['ingredient_name'];
                $accompany->quantity = $toAccompany['quantity'];
                $accompany->unit = $toAccompany['unit'];
                $accompany->save();
            }

            foreach ($data['materials'] as $material) {

                $recipeMaterial = new RecipeMaterial;
                $recipeMaterial->recipe_id = $recipe->id;
                $recipeMaterial->material_name = $material['material_name'];
                $recipeMaterial->quantity = $material['quantity'];
                $recipeMaterial->unit = $material['unit'];
                $recipeMaterial->save();
            }

            return redirect('/admin/recipe-index')->with('success', 'Recipe Inserted Successfully !!!');
        }
        return view('admin.recipeDiet.recipe.create', ['mealTypeData' => $mealTypeData, 'ingredientCategory' => $ingredientCategory, 'ingredientList' => $ingredientList]);
    }

    public function update(Request $request, $slug, $id)
    {
        Session::put('page', 'recepies');
        $recipe = Recipe::where(['id' => $id])->with('ingredient', 'preparation', 'nutrition', 'to_accompany', 'recipe_material')->first()->toArray();
        // echo "<pre>"; print_r($recipe); die;
        $ingredientList = Ingredient::get()->toArray();
        if ($request->isMethod('post')) {
            $data = $request->all();
            // echo "<pre>"; print_r($data); die;
            $rules = [
                'title' => ['required', 'string', 'max:255'],
                'cooking_time' => ['required'],
                'overall_time' => ['required'],
                'about' => ['required'],
                'tags' => ['required'],
                'meal_type' => ['required'],
            ];

            $message = [
                'title.required' => "Title is required.",
                'cooking_time.required' => "Please enter approx time it will take to cook this recipe.",
                'overall_time.required' => "Please enter overall time it will take to cook this recipe.",
                'about.required' => "Write in some words about this recipe.",
                'tags.required' => "Please enter atleast one tag",
                'meal_type.required' => "Please select atleast one meal type.",
            ];
            $validator = Validator::make($data, $rules, $message);
            $input = $request->except(['_token']);
            if ($validator->fails()) {
                return redirect()->back()->withInput()->withErrors($validator->getMessageBag());
            }

            if (empty($data['status'])) {
                $status = 0;
            } else {
                $status = 1;
            }

            if (empty($data['free_access'])) {
                $free_access = 0;
            } else {
                $free_access = 1;
            }

            if (empty($data['special_recipe'])) {
                $special_recipe = 0;
            } else {
                $special_recipe = 1;
            }

            if (empty($data['is_person'])) {
                $person = 0;
                $noOfPerson = 1;
            } else {
                $person = 1;
                $noOfPerson = $data['no_of_persons'];
            }

            if (!isset($data['description']) || empty($data['description'])) {
                $data['description'] = "";
            }

            if (!isset($data['objective']) || empty($data['objective'])) {
                $data['objective'] = "";
            }

            $slug = Str::slug($data['title']);

            // Recipe Image
            if (!isset($data['picture']) || empty($data['picture'])) {
                $data['picture'] = $recipe['picture'];
            } else {
                if (!Storage::disk('public')->exists("/recipes/image")) {
                    Storage::disk('public')->makeDirectory("/recipes/image"); //creates directory
                }
                if (empty($recipe['picture'])) {
                    $recipeImage = time() . '.' . $data['picture']->extension();
                } else {
                    Storage::delete($recipe['picture']);
                    $recipeImage = time() . '.' . $data['picture']->extension();
                }
                $request->picture->storeAs("recipes/image", $recipeImage, 'public');
                $data['picture'] = "recipes/image/$recipeImage";
            }

            if (!isset($data['prep_video']) || empty($data['prep_video'])) {
                $data['prep_video'] = "";
            }

            $updateProgram = Recipe::where(['id' => $id])->update(['title' => $data['title'], 'tags' => implode("|", $data['tags']), 'meal_type_id' => implode("|", $data['meal_type']), 'picture' => $data['picture'], 'cooking_time' => $data['cooking_time'], 'overall_time' => $data['overall_time'], 'slug' => $slug, 'about' => $data['about'], 'prep_video' => $data['prep_video'], 'is_person' => $person, 'no_of_person' => $noOfPerson, 'status' => $status, 'free_access' => $free_access, 'special_recipe' => $special_recipe]);

            // Preparation Steps
            $prepId = [];
            foreach ($data['preparation'] as $key => $preparationStep) {
                $prepId[] = $preparationStep['prepId'];

                if (!empty($preparationStep['prepId'])) {
                    RecipePreparationSteps::where(['id' => $preparationStep['prepId'], 'recipe_id' => $id])->update(['prep_steps' => $preparationStep['stepTitle'], 'prep_description' => $preparationStep['stepDescription']]);
                } else {
                    // dd($preparationStep['preparation']);
                    $recipePrep = new RecipePreparationSteps;
                    $recipePrep->recipe_id = $id;
                    $recipePrep->prep_steps = $preparationStep['stepTitle'];
                    $recipePrep->prep_description = $preparationStep['stepDescription'];
                    $recipePrep->save();
                }
            }
            RecipePreparationSteps::whereNotIn('id', $prepId)->where(['recipe_id' => $id])->delete();
            // End Preparation Step

            // Nutrition and Ingredients
            $recipeNutritionId = [];
            $ingredientId = [];
            foreach ($data['ingredientsWithNutrition'] as $key => $recipeNutrition) {
                if (!empty($recipeNutrition['recipe_nutrition_id'])) {
                    $recipeNutritionId[] = $recipeNutrition['recipe_nutrition_id'];
                    RecipeNutrition::where(['id' => $recipeNutrition['recipe_nutrition_id'], 'recipe_id' => $id])->update(['kcal' => $recipeNutrition['kcal'], 'protien' => $recipeNutrition['protein'], 'fat' => $recipeNutrition['fat'], 'carbs' => $recipeNutrition['carbs']]);
                    // Ingredients
                    foreach ($ingredientList as $commonIngrdient) {
                        if(!empty($recipeNutrition['ingredients'])){
                            foreach ($recipeNutrition['ingredients'] as $key => $ingredient) {
                                if ($commonIngrdient['id'] == $ingredient['ingredient_id']) {

                                    if (!empty($ingredient['recipeIngredientId'])) {
                                        $ingredientId[] = $ingredient['recipeIngredientId'];
                                        RecepieIngredients::where(['id' => $ingredient['recipeIngredientId'], 'recepie_id' => $id, 'nutrition_id' => $recipeNutrition['recipe_nutrition_id']])->update(['ingredient_id' => $ingredient['ingredient_id'], 'name' => $commonIngrdient['name'], 'quantity' => $ingredient['quantity'], 'unit' => $ingredient['unit'], 'category_id' => $ingredient['category_id']]);
                                    } else {
                                        $recipeIngredient = new RecepieIngredients;
                                        $recipeIngredient->recepie_id = $id;
                                        $recipeIngredient['ingredient_id'] = $ingredient['ingredient_id'];
                                        $recipeIngredient['nutrition_id'] = $recipeNutrition['recipe_nutrition_id'];
                                        $recipeIngredient->name = $commonIngrdient['name'];
                                        $recipeIngredient->quantity = $ingredient['quantity'];
                                        $recipeIngredient->unit = $ingredient['unit'];
                                        $recipeIngredient->category_id = $ingredient['category_id'];
                                        $recipeIngredient->save();
                                        $ingredientId[] = $recipeIngredient['id'];
                                    }
                                }
                            }
                        }
                    }
                } else {
                    $nutrition = new RecipeNutrition;
                    $nutrition->recipe_id = $id;
                    $nutrition->kcal = $recipeNutrition['kcal'];
                    $nutrition->protien = $recipeNutrition['protein'];
                    $nutrition->fat = $recipeNutrition['fat'];
                    $nutrition->carbs = $recipeNutrition['carbs'];
                    $nutrition->save();

                    $recipeNutritionId[] = $nutrition['id'];

                    foreach ($ingredientList as $commonIngrdient) {
                        if(!empty($recipeNutrition['ingredients'])) {
                            foreach ($recipeNutrition['ingredients'] as $key => $ingredient) {
                                if ($commonIngrdient['id'] == $ingredient['ingredient_id']) {

                                    $recipeIngredient = new RecepieIngredients;
                                    $recipeIngredient->recepie_id = $id;
                                    $recipeIngredient['ingredient_id'] = $ingredient['ingredient_id'];
                                    $recipeIngredient['nutrition_id'] = $nutrition['id'];
                                    $recipeIngredient->name = $commonIngrdient['name'];
                                    $recipeIngredient->quantity = $ingredient['quantity'];
                                    $recipeIngredient->unit = $ingredient['unit'];
                                    $recipeIngredient->category_id = $ingredient['category_id'];
                                    $recipeIngredient->save();

                                    $ingredientId[] = $recipeIngredient['id'];
                                }
                            }
                        }
                    }
                }
            }
            RecipeNutrition::whereNotIn('id',  $recipeNutritionId)->where(['recipe_id' => $id])->delete();
            RecepieIngredients::whereNotIn('id', $ingredientId)->where(['recepie_id' => $id])->delete();

            $accompId = [];
            foreach ($data['accompany'] as $key => $accompany) {
                $accompId[] = $accompany['accompId'];

                if (!empty($accompany['accompId'])) {
                    ToAccompany::where(['id' => $accompany['accompId'], 'recipe_id' => $id])->update(['ingredient_name' => $accompany['ingredient_name'], 'quantity' => $accompany['quantity'], 'unit' => $accompany['unit']]);
                } else {
                    // dd($preparationStep['preparation']);
                    $toAccompany = new ToAccompany;
                    $toAccompany->recipe_id = $id;
                    $toAccompany->ingredient_name = $accompany['ingredient_name'];
                    $toAccompany->quantity = $accompany['quantity'];
                    $toAccompany->unit = $accompany['unit'];
                    $toAccompany->save();
                }
            }
            ToAccompany::whereNotIn('id', $accompId)->where(['recipe_id' => $id])->delete();

            $matId = [];
            foreach ($data['materials'] as $key => $material) {
                $matId[] = $material['matId'];
                if (!empty($material['matId'])) {
                    RecipeMaterial::where(['id' => $material['matId'], 'recipe_id' => $id])->update(['material_name' => $material['material_name'], 'quantity' => $material['quantity'], 'unit' => $material['unit']]);
                } else {

                    $recipeMaterial = new RecipeMaterial;
                    $recipeMaterial->recipe_id = $id;
                    $recipeMaterial->material_name = $material['material_name'];
                    $recipeMaterial->quantity = $material['quantity'];
                    $recipeMaterial->unit = $material['unit'];
                    $recipeMaterial->save();
                }
            }
            RecipeMaterial::whereNotIn('id', $matId)->where(['recipe_id' => $id])->delete();

            return redirect('/admin/recipe-index')->with('success', 'Recipe updated successfully !!!');
        }
        $mealTypeData = MealType::get()->toArray();
        $ingredientCategory = IngredientCategory::get()->toArray();

        return view('admin.recipeDiet.recipe.edit')->with(compact('recipe', 'mealTypeData', 'ingredientCategory', 'ingredientList'));
    }

    public function updateRecipeStatus(Request $request)
    {
        Session::put('page', 'recepies');
        if ($request->ajax()) {
            $data = $request->all();

            // echo "<pre>"; print_r($data); die;
            if ($data['status'] == "Active") {
                $status = 0;
            } else {
                $status = 1;
            }
            Recipe::where(['id' => $data['recipe_id']])->update(['status' => $status]);

            return response()->json(['status' => $status, 'recipe_id' => $data['recipe_id']]);
        }
    }

    public function destroy($slug, $id)
    {
        Session::put('page', 'recepies');
        Recipe::where(['slug' => $slug, 'id' => $id])->delete();
        return redirect()->back();
    }

    public function deleteRecipeIngredient($id = null)
    {
        RecepieIngredients::where(['id' => $id])->delete();
        sleep(1);
        return redirect()->back()->with('success', 'Ingredient has been deleted successfully!!!');
    }
}
