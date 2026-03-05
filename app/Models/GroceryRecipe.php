<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroceryRecipe extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'recipe_id',
        'nutrition_id'
    ];

    public function recipes()
    {
        return $this->belongsTo(Recipe::class, 'recipe_id', 'id');
    }

    public function recipe_nutrition()
    {
        return $this->belongsTo(RecipeNutrition::class, 'nutrition_id', 'id');
    }

    public function grocery_ingredients(){
        return $this->hasMany(CommonGroceryIngredient::class, 'recipe_id', 'recipe_id');
    }
}
