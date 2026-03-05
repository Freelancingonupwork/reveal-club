<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromoCodeUsage extends Model
{
    use HasFactory;

        protected $fillable = [
            'user_id',
            'promo_code_id',
            'applied_date',
            'total_discount_price',
        ];
    
        // public function user()
        // {
        //     return $this->belongsTo(User::class);
        // }
    
        // public function promoCode()
        // {
        //     return $this->belongsTo(PromoCode::class, 'promo_code_id');
        // }
}
