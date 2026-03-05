<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class userMilestone extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'user_id',
        'milestone_id',
        'milestone_type'
    ];
}
