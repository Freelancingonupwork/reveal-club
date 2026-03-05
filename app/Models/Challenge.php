<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Challenge extends Model
{
    use HasFactory;

    public function challengeDays()
    {
        return $this->hasMany(ChallengeDay::class, 'challenge_id', 'id');
    }
}
