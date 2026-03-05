<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsersTargetMeasurement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'weight',
        'neck',
        'bicep',
        'chest',
        'waist',
        'hips',
        'thighs',
        'calfs',
        'age',
        'gender'
    ];
}
