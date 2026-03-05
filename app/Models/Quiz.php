<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'ques_title',
        'quiz_group_id',
        'slug',
        'ques_description',
        'is_ques_image',
        'ques_image',
        'ques_type',
        'ques_for',
        'cardio_id',
        'is_sales_page',
        'is_calory_calc',
        'is_another_ques',
        'ques_id',
        'is_have_transition',
        'transition_logic',
        'answer_type',
        'quiz_position',
        'ques_for_gender',
        'answer_format',
        'have_instruction',
        'instruction_message',
        'is_turnstile_enabled',
        'is_google_analytics',
        'google_analytic_script',
        'is_active',
    ];

    public function answers()
    {
        return $this->hasMany(Answer::class, 'question_id', 'id');
    }

    public function quiz_group()
    {
        return $this->belongsTo(QuizGroup::class, 'quiz_group_id', 'id')->orderBy('quiz_group_order');
    }
}
