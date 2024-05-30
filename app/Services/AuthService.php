<?php

namespace App\Services;

use App\Models\DeviceToken;
use App\Models\Role;
use App\Models\User;

class AuthService
{
    public function create($request){
        $request->merge([
            'password' => bcrypt($request->password),
            'role_id' => Role::USER
        ]);

        $user = User::create($request->all());
        return customResponse(true, "User created successfully.", 201, $user);
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

            if ($request->device_id && $request->device_type) {
                DeviceToken::create([
                    'user_id'     => auth()->id(),
                    'device_id'   => $request->device_id,
                    'device_type' => $request->device_type
                ]);
            }

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