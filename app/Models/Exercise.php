<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exercise extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'title_for_frontend',
        'instructions',
        'audio',
        'rest_period',
        'body_zone',
        'exercise_type',
        'video',
        'duration',
        'no_of_repetition',
        'range_of_repetition',
        'exercise_form',
        'gif',
        'status',
    ];

    public function session_exercise()
    {
        return $this->belongsTo(SessionExercise::class);
    }
}
