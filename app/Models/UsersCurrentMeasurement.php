<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsersCurrentMeasurement extends Model
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
        'gender',
        'last_modified_date'
    ];

    public function initialMeasurement()
    {
        return $this->hasOne(UsersInitialMeasurement::class, 'user_id', 'user_id')->orderBy('id', 'desc');
    }

    public function targetMeasurement()
    {
        return $this->hasOne(UsersTargetMeasurement::class, 'user_id', 'user_id')->orderBy('id', 'desc');
    }
}
