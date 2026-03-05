<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLevel extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'level',
        'title',
        'description',
        'improvement_per',
        'video_title',
        'video_description',
        'video_url'
    ];

    public function userLevelTasks()
    {
        return $this->hasMany(UserLevelTask::class, 'user_level_id');
    }
}
