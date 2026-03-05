<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promocode extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'promocode',
        'description',
        'start_date',
        'end_date',
        'status',
        'discount_value',
        'discount_type',
    ];
}
