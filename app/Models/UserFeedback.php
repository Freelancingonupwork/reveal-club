<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserFeedback extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'user_id',
        'screen_id',
        'feedback',
    ];
}
