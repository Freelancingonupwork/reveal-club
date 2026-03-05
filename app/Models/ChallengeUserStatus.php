<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChallengeUserStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'challenge_day_id',
        'status',
        'complete_date',
        'challenge_id',
        'challenge_level_id',
        'completed_day'
    ];

    public function exerciseOfChallenge()
    {
        return $this->belongsTo(ExerciseOfChallenge::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function challengeDay()
    {
        return $this->belongsTo(ChallengeDay::class, 'challenge_day_id');
    }
}
