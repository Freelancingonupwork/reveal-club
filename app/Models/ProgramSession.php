<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramSession extends Model
{
    use HasFactory;

    protected $fillable = ['program_id', 'session_id', 'session_week'];

    public function session()
    {
        return $this->hasMany(Session::class, 'id', 'session_id')->with('session_exercises');
    }

    public function session_exercises()
    {
        return $this->hasMany(SessionExercise::class, 'session_id', 'session_id');
    }
}
