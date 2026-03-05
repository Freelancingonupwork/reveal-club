<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecipeNutrition extends Model
{
    use HasFactory;

    protected $fillable = [
        'recepie_id',
        'kcal',
        'protien',
        'fat',
        'carbs',
    ];

    public function nutritioningredients()
    {
        return $this->hasMany(RecepieIngredients::class, 'nutrition_id', 'id');
    }
}
