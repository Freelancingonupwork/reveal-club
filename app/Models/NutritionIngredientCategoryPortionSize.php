<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NutritionIngredientCategoryPortionSize extends Model
{
    use HasFactory;
    protected $fillable = [
        'nutrition_ingredient_category_id',
        'name',
        'quantity',
        'units',
    ];
    public function nutritionCategory()
    {
        return $this->belongsTo(NutritionIngredientCategory::class);
    }
}
