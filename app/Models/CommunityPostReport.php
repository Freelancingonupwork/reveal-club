<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommunityPostReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'user_id',
        'reason',
        'description',
        'mark_as_solved',
    ];
    public function post()
    {
        return $this->belongsTo(CommunityPosts::class, 'post_id');
    }

    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }
}
