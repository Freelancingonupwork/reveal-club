<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserReferenceAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'user_answered_id',
        'question_id',
        'user_answer_id',
        'quiz_group_id',
        'answer_type',
        'key',
        'value',
        'answer',
        'email_marketing',
        'session_id'
    ];
}
