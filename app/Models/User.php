<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'first_name',
        'last_name',
        'type',
        'email',
        'email_verified_at',
        'mobile',
        'gender',
        'date_of_birth',
        'height',
        'password',
        'avatar',
        'login_type',
        'device_type',
        'device_token',
        'login_key',
        'apple_id',
        'social_key',
        'isQuestionsAttempted',
        'isSubscribedUser',
        'iosSubscribedUser',
        'status',
        'referral_source',
        'country',
        'city',
        'address',
        'postal_code',
        'company',
        'remember_token',
        'intercom_id',
        'intercom_hash',
        'timezone',
        'level',
        'previous_level_popup_shown',
        'last_opened_at',
        'streak'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
    
    public function challengeUserStatuses()
    {
        return $this->hasMany(ChallengeUserStatus::class, 'user_id', 'id');
    }
}
