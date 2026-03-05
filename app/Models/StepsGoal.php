<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StepsGoal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'steps_goal',
        'distance',
        'steps_count',
        'steps_completed',
        'steps_remaining',
        'goal_date',
        'goal_time',
        'activity_level',
        'activity_factor',
        'isCompleted'
    ];
}
