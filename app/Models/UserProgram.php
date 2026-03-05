<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProgram extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'program_id',
        'join_date',
        'status',
        'user_streak'
    ];

    public function program(){
        return $this->hasOne(Program::class, 'id', 'program_id');
    }
}
