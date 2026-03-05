<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskMilestone extends Model
{
    use HasFactory;

    protected $fillable =[
        'milestone_type',
        'milestone_title',
        'milestone_description',
        'milestone_count',
    ];
}
