<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommunityPosts extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'topic_id',
        'content',
        'image',
        'colour_theme',
        'content_type',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function topic()
    {
        return $this->belongsTo(CommunityPostTopic::class, 'topic_id');
    }

    public function comments()
    {
        return $this->hasMany(CommunityPostComment::class, 'post_id');
    }

    public function likes()
    {
        return $this->hasMany(CommunityPostLike::class, 'post_id');
    }

    // Accessor for comments count
    public function getCommentsCountAttribute()
    {
        return $this->comments()->count();
    }

    // Accessor for likes count
    public function getLikesCountAttribute()
    {
        return $this->likes()->count();
    }

    public function reports()
    {
        return $this->hasMany(CommunityPostReport::class, 'post_id');
    }
}
