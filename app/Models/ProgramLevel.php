<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class ProgramLevel extends Model
{
    use HasFactory;

    protected $fillable = ['level_title', 'slug', 'start_range', 'end_range'];

}
