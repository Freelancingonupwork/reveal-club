<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NutritionIngredient extends Model
{
    use HasFactory;

    protected $fillable = [
        'barcode',
        'name',
        'kcal',
        'protein',
        'fats',
        'carbs',
        'sugar',
        'salt',
        'image_url',
        'small_image_url',
        'slug',
        'status',
        'mark_as_reviewed',
        'user_id'
    ];

    protected $table = 'nutrition_ingredients';

    public function barcodes()
    {
        return $this->hasMany(NutritionIngredientBarcode::class, 'nutrition_ingredient_id');
    }

    public function units()
    {
        return $this->hasMany(NutritionIngredientUnit::class, 'nutrition_ingredient_id');
    }
}
