<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class UsersAppearanceInfo extends Model
{
    use HasFactory;

    public function initialMeasurement()
    {
        return $this->hasOne(UsersInitialMeasurement::class, 'user_id', 'user_id');
    }

    public function targetMeasurement()
    {
        return $this->hasOne(UsersTargetMeasurement::class, 'user_id', 'user_id');
    }

    public function currentMeasurement()
    {
        return $this->hasOne(UsersCurrentMeasurement::class, 'user_id', 'user_id');
    }
}
