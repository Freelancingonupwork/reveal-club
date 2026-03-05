<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Activity extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'default_burnout_per_hour',
        'image',
        'status',
        'description'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'default_burnout_per_hour' => 'decimal:2',
        'status' => 'boolean'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn ($value) =>
                $value ? asset(Storage::url($value)) : ''

        );
    }

    protected function description(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ?? ''
        );
    }

    // Activity.php
    public function trackedActivities()
    {
        return $this->hasMany(TrackedActivity::class);
    }

}
