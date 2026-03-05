<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChallengeDay extends Model
{
    use HasFactory;
    
    public function challenge()
    {
        return $this->belongsTo(Challenge::class, 'challenge_id', 'id');
    }

    public function exercisesOfChallenge()
    {
        return $this->hasMany(ExerciseOfChallenge::class, 'challenge_day_id', 'id'); 
    }

    public function challengeLevel()
    {
        return $this->belongsTo(ChallengeLevel::class, 'challenge_level_id', 'id'); 
    }

    public function challengeUserStatus()
    {
        return $this->hasMany(ChallengeUserStatus::class, 'exercise_of_challenge_id', 'id');
    }
}
