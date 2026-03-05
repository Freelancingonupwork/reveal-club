<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExerciseOfChallenge extends Model
{
    use HasFactory;

    public function exercise()
    {
        return $this->belongsTo(ChallengeExercise::class, 'exercise_id', 'id');
    }
}
