<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommunityPostCommentReport extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'comment_or_reply_id',
        'user_id',
        'is_comment_or_reply',
        'reason',
        'feedback',
        'mark_as_solved'
    ];

    public function user() {
        return $this->belongsTo(User::class)->select('id', 'name', 'avatar');
    }

    public function comment()
    {
        if ($this->is_comment_or_reply == 'comment') {
            return $this->belongsTo(CommunityPostComment::class, 'comment_or_reply_id','id');
        }
    }
    
    public function reply()
    {
        if ($this->is_comment_or_reply == 'reply') {
            return $this->belongsTo(CommunityPostCommentReply::class, 'comment_or_reply_id','id');
        }
    }
}
