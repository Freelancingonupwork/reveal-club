<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'category_id',
        'program_tag',
        'title',
        'description',
        'objective',
        'slug',
        'video',
        'level_id',
        'program_level_points',
        'body_area',
        'duration',
        'frequency',
        'cardio_id',
        'muscle_strength_id',
        'free_access',
        'status'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function program_session()
    {
        return $this->hasMany(ProgramSession::class, 'program_id', 'id')->with('session_exercises');
    }
    public function level()
    {
        return $this->belongsTo(ProgramLevel::class, 'level_id');
    }
}
