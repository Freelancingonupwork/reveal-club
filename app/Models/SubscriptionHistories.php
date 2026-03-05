<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionHistories extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'plan_id',
        'customer_id',
        'subscription_id',
        'payment_method_type',
        'amount',
        'status',
        'start_date',
        'end_date',
        'taken_trial',
        'taken_discount',
    ];
}
