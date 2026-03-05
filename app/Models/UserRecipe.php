<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRecipe extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'meal_type_id',
        'quantity',
        'kcal',
        'description',
        'image',
        'totalKcal',
        'totalQuantity'
    ];

    public function ingredients(){
        return $this->hasMany(UserRecipeIngredient::class, 'user_recipes_id', 'id');
    }
}
