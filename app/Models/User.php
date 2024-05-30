<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // Constants
    public const ACTIVE = 1;
    public const INACTIVE = 0;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
        'avatar',
        'device_id',
        'device_type',
        'role_id',
        'bio',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Functions
    public function getUserByEmail($email){
        return $this->where('email', $email)->first();
    }

    public function resetPassword($request){
        $user = $this->getUserByEmail($request->email);
        $user->password = bcrypt($request->password);
        $user->save();
        
        return customResponse(true, "Password reset successfully.");
    }
}
