<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'quiz_group_order',
        'color'
    ];
}
