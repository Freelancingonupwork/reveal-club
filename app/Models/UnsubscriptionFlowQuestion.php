<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnsubscriptionFlowQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'screen_title',
        'show_title',
        'slug',
        'is_screen_image',
        'screen_image',
        'screen_description',
        'button_text',
        'is_multiple_button',
        'multiple_buttons_value',
        'have_offer',
        'offer_in_per',
        'screen_position',
        'screen_type',
        'feedback_type',
    ];

    public function feedback()
    {
        return $this->hasMany(feedback_question::class, 'screen_id', 'id');
    }
    protected $casts = [
        'multiple_buttons_value' => 'array',
    ];
}
