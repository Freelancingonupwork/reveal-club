<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRecipeIngredient extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_recipes_id',
        'ingredient_id',
        'quantity',
        'kcal',
        'protein',
        'fats',
        'carbs',
    ];
}
