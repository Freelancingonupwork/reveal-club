<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChallengeExercise extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'title_for_frontend',
        'instructions',
        'video_url_exercise',
        'video_url_presentation',
        'gif',
        'status',
    ];
}
