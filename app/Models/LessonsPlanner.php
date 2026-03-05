<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonsPlanner extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'lesson_id',
        'is_logged',
        'date'
    ];
}
