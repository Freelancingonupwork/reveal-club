<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NutritionIngredientUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'nutrition_ingredient_id',
        'size_key',
        'value',
        'units',
    ];

    public function nutritionIngredient()
    {
        return $this->belongsTo(NutritionIngredient::class, 'nutrition_ingredient_id');
    }
}
