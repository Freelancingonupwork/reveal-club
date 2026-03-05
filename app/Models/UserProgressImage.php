<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProgressImage extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'before_image_id',
        'after_image_id',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'before_image_id' => 'integer',
        'after_image_id'  => 'integer',
        'is_active'       => 'boolean',
    ];

    // Accessors to include in JSON
    protected $appends = ['before_image', 'after_image'];

    /**
     * Hide raw relations
     */
    protected $hidden = [
        'beforeImage',
        'afterImage',
    ];

    /* -----------------------------
     | Relationships
     |-----------------------------*/

    public function user()
    {
        return $this->belongsTo(User::class);
    }

     public function beforeImage()
    {
        return $this->belongsTo(UsersAppearanceInfo::class, 'before_image_id');
    }

    public function afterImage()
    {
        return $this->belongsTo(UsersAppearanceInfo::class, 'after_image_id');
    }

    /* -----------------------------
     | Attribute Normalization
     |-----------------------------*/

    public function getBeforeImageAttribute()
    {
        if ($this->relationLoaded('beforeImage') && $this->getRelation('beforeImage')) {
            $image = $this->getRelation('beforeImage')->toArray();

            // Remove unwanted fields
            unset($image['created_at'], $image['updated_at'], $image['deleted_at']);

            // Add full URL
            $image['image'] = $image['image'] ? url('storage/' . $image['image']) : null;

            return $image;
        }

        return (object) [];
    }

    public function getAfterImageAttribute()
    {
        if ($this->relationLoaded('afterImage') && $this->getRelation('afterImage')) {
            $image = $this->getRelation('afterImage')->toArray();

            // Remove unwanted fields
            unset($image['created_at'], $image['updated_at'], $image['deleted_at']);

            // Add full URL
            $image['image'] = $image['image'] ? url('storage/' . $image['image']) : null;

            return $image;
        }

        return (object) [];
    }
    
    public function getBeforeImageIdAttribute($value): int
    {
        return $value ? (int) $value : 0;
    }

    public function getAfterImageIdAttribute($value): int
    {
        return $value ? (int) $value : 0;
    }
}
