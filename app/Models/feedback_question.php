<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class feedback_question extends Model
{
    use HasFactory;

    public function questions(){
        return $this->belongsTo(UnsubscriptionFlowQuestion::class, 'screen_id', 'id');
    }
}
