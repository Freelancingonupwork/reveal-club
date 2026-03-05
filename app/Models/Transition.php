<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transition extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'is_trans_image',
        'transition_image',
        'is_term_and_cond',
        'trans_description',
        'status',
    ];
}
