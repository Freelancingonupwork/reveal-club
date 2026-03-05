<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SessionExercise extends Model
{
    use HasFactory;

    public function session()
    {
        return $this->hasMany(Session::class, 'id', 'session_id')->with('materials');
    }

    public function exercise()
    {
        return $this->hasMany(Exercise::class, 'id', 'session_exercise_id');
    }
}
