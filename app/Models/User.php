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

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
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

    public function add($request){
        $request->merge([
            'password' => bcrypt($request->password),
            'role_id' => Role::USER
        ]);

        $user = $this->create($request->all());
        return $user;
    }

    public function login($request){
        if(auth()->attempt([
            'email' => $request->email,
            'password' => $request->password
        ])){
            if(auth()->user()->status == User::INACTIVE){
                return customResponse(false, "Your account is not active.", 422);
            }
            if(auth()->user()->role_id == Role::ADMIN){
                return customResponse(false, "Invalid email or password.", 422);
            }

            $this->find(auth()->id())->update([
                'device_id'   => $request->device_id,
                'device_type' => $request->device_type
            ]);

            $token = $request->user()->createToken('main')->plainTextToken;
            return customResponse(true, "login successfull!", 200, [
                'user' => auth()->user(),
                'access_token' => $token
            ]);
        }
        return customResponse(false, "Invalid email or password.", 422);
    }

    public function login_admin($request){
        if(auth()->attempt([
            'email' => $request->email,
            'password' => $request->password,
            'role_id' => Role::ADMIN
        ])){
            $token = $request->user()->createToken('main')->plainTextToken;
            return customResponse(true, "login successfull!", 200, [
                'user' => User::find(auth()->id()),
                'access_token' => $token
            ]);
        }
        return customResponse(false, "Invalid email or password.", 422);
    }
}
