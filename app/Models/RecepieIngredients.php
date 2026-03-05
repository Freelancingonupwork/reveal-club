<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecepieIngredients extends Model
{
    use HasFactory;

    protected $fillable = [
        'recepie_id',
        'ingredient_id',
        'nutrition_id',
        'name',
        'quantity'
    ];

    public function recipe(){
        return $this->belongsTo(Recipe::class, 'recepie_id', 'id');
    }

    public function unit()
    {
        return $this->belongsTo(UnitConversion::class, 'unit_id', 'id');
    }
}
