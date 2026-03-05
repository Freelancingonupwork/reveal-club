<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'answer_type',
        'answer_format',
        'ques_answers',
        'answer_img',
        'cardio_and_muscle_id',
        'label',
        'is_numeric',
        'have_transition',
        'transition_id',
        'transition_logic',
        'ques_type',
        'ans_points',
    ];

    public function transitions()
    {
        $transitionIds = explode('|', $this->transition_id);
    
        return $this->belongsTo(Transition::class, 'transition_id', 'id')
            ->orderByRaw("FIELD(id, " . implode(',', array_map('intval', $transitionIds)) . ")");
    }

    public function question(){
        return $this->belongsTo(Quiz::class, 'question_id', 'id');
    }
}
