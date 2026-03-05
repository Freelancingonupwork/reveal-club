<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrackedActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'activity_id',
        'duration_minutes',
        'activity_date',
        'notes',
        'calories_burned'
    ];

    protected $casts = [
        'activity_date' => 'date:Y-m-d',
        'duration_minutes' => 'decimal:2',
        'calories_burned' => 'decimal:2'
    ];

    protected $hidden = [
        'notes',
        'created_at',
        'updated_at',
    ];

    protected function notes(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ?? ''
        );
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }
    
}
