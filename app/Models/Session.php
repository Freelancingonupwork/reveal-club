<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    use HasFactory;

    public function program()
    {
        return $this->belongsTo(Program::class, 'program_id', 'id')->with('category');
    }

    public function materials()
    {
        return $this->hasMany(SessionMaterial::class, 'session_id', 'id');
    }

    public function session_exercises()
    {
       return $this->hasMany(SessionExercise::class);
    }
}
