<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLevelTaskLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'user_id',
        'level_task_type',
        'completed_count'
    ];
}
