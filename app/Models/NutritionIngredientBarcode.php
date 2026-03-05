<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NutritionIngredientBarcode extends Model
{
    use HasFactory;

    protected $fillable = [
        'nutrition_ingredient_id',
        'barcode',
    ];

}
