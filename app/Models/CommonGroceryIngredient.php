<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommonGroceryIngredient extends Model
{
    use HasFactory;

    protected $fillable = [
        'recipe_id',
        'recipe_ingredient_id',
        'ingredient_category_id',
        'ingredient_nutrition_id',
        'user_id',
        'ingredient_name',
        'ingredient_quantity',
        'ingredient_unit',
        'no_of_person',
        'isPurchased',
        'is_personalised'
    ];

    public function ingredient_category()
    {
        return $this->belongsTo(IngredientCategory::class, 'ingredient_category_id', 'id');
    }

    public function recipe()
    {
        return $this->belongsTo(Recipe::class, 'recipe_id', 'id')->with('ingredient');
    }
}
