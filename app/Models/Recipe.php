<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    use HasFactory;

    public function nutrition()
    {
        return $this->hasMany(RecipeNutrition::class, 'recipe_id', 'id');
    }

    public function ingredient(){
        return $this->hasMany(RecepieIngredients::class, 'recepie_id', 'id');
    }

    public function preparation()
    {
        return $this->hasMany(RecipePreparationSteps::class, 'recipe_id', 'id');
    }

    public function to_accompany()
    {
        return $this->hasMany(ToAccompany::class, 'recipe_id', 'id');
    }

    public function recipe_material()
    {
        return $this->hasMany(RecipeMaterial::class, 'recipe_id', 'id');
    }
}
