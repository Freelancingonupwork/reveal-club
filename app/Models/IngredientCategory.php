<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IngredientCategory extends Model
{
    use HasFactory;

    public function recipeIngredient()
    {
        return $this->hasMany(RecepieIngredients::class, 'category_id', 'id');
    }
}
