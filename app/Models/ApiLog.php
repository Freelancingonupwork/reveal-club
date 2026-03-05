<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiLog extends Model
{
    use HasFactory;
    protected $fillable = [
        'action_name',
        'params',
        'response',
        'user_id',
        'session_id',
    ];
    
    protected $casts = [
        'params' => 'array',
        'response' => 'array',
    ];
}
