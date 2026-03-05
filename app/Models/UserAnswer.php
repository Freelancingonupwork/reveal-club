<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'question_id',
        'quiz_group_id',
        'answer_id',
        'question_type',
        'answer_type',
        'session_id'
    ];

    public function userReferenceAnswer(){
        return $this->belongsTo(UserReferenceAnswer::class, 'id', 'user_answered_id');
    }
}
