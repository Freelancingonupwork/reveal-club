<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLevelTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'user_level_id',
        'task_type',
        'total_count',
        'duration'
    ];

    public function userLevel()
    {
        return $this->belongsTo(UserLevel::class, 'user_level_id');
    }
}
