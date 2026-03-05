<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NutritionIngredientCategory extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'slug',
        'image_url',
        'status',
    ];

    public function portionSizes()
    {
        return $this->hasMany(NutritionIngredientCategoryPortionSize::class, 'nutrition_ingredient_category_id');
    }
}
