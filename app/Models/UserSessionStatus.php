<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSessionStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'program_id',
        'program_session_id',
        'session_id',
        'session_week',
        'user_session_status',
    ];

}
