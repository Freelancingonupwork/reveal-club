<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsersSubscriptions extends Model
{
    use HasFactory;

    protected $table = "users_subscriptions";

    protected $fillable = [
        'user_id',
        'plan_id',
        'next_plan_id',
        'customer_id',
        'subscription_id',
        'payment_method_type',
        'amount',
        'billing_cycle',
        'status',
        'is_refunded',
        'is_applied_for_trial',
        'is_applied_for_cancel',
        'is_applied_for_discount',
        'start_date',
        'end_date',
    ];
    
    /**
     * Relationship: Subscription belongs to a User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
