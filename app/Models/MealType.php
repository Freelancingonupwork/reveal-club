<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MealType extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'meal_type',
        'image',
        'slug',
        'visible_in_tracker',
        'is_popular',
    ];
}
