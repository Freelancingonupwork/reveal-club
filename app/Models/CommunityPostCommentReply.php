<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommunityPostCommentReply extends Model
{
    use HasFactory;

    protected $fillable = [
        'comment_id',
        'parent_id',
        'user_id',
        'reply'
    ];

    public function user() {
        return $this->belongsTo(User::class)->select('id', 'name', 'avatar');
    }

    public function comment() {
        return $this->belongsTo(CommunityPostComment::class, 'comment_id', 'id');
    }

    // 👇 Parent reply (the one this reply is replying to)
    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    // 👇 Child replies (all nested replies to this reply)
    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->with('user', 'children');
    }

    public function reports(){
        return $this->hasMany(CommunityPostCommentReport::class, 'comment_or_reply_id','id')->where('is_comment_or_reply', 'reply');
    }
}
