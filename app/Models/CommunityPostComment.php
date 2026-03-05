<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommunityPostComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'user_id',
        'comment'
    ];

    public function commentReplies()
    {
        return $this->hasMany(CommunityPostCommentReply::class, 'comment_id', 'id')
                ->whereNull('parent_id')
                ->with(['user', 'children']);
    }

    public function user() {
        return $this->belongsTo(User::class)->select('id', 'name', 'avatar');
    }

    public function post() {
        return $this->belongsTo(CommunityPosts::class, 'post_id', 'id');
    }
  
    public function reports(){
        return $this->hasMany(CommunityPostCommentReport::class, 'comment_or_reply_id','id')->where('is_comment_or_reply', 'comment');
    }
}
