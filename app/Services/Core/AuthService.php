<?php

namespace App\Services\Core;

use App\Helpers\Constant;
use App\Models\DeviceToken;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;

class AuthService
{
    public function create($request){
        return User::create($request->all());
    }

    public function login($request){
        if(auth()->attempt([
            'email' => $request->email,
            'password' => $request->password
        ])){
            if(auth()->user()->is_active == Constant::INACTIVE){
                throw new AuthorizationException("Your account is not active.");
            }
            if(auth()->user()->role == Constant::ADMIN){
                throw new AuthenticationException("Invalid email or password.");
            }

            if ($request->device_id && $request->device_type) {
                DeviceToken::create([
                    'user_id'     => auth()->id(),
                    'device_id'   => $request->device_id,
                    'device_type' => $request->device_type
                ]);
            }

            $token = $request->user()->createToken('main')->plainTextToken;
            return [
                'user' => auth()->user(),
                'access_token' => $token
            ];
        }
        throw new AuthenticationException("Invalid email or password.");
    }

    public function login_admin($request){
        if(auth()->attempt([
            'email'    => $request->email,
            'password' => $request->password,
            'role'     => Constant::ADMIN
        ])){
            $token = $request->user()->createToken('main')->plainTextToken;
            return [
                'user' => User::find(auth()->id()),
                'access_token' => $token
            ];
        }
        throw new AuthenticationException("Invalid email or password.");
    }
}