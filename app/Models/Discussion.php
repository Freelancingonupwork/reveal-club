<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Discussion extends Model
{
    use HasFactory;

    public function reply()
    {
        return $this->hasMany(DiscussionReply::class, 'discussion_id', 'id')->with('user');
    }

    public function userReply()
    {
        return $this->hasMany(DiscussionReply::class, 'discussion_id', 'id')->where(['user_id' => Auth::guard('user')->user()->id]);
    }
}
