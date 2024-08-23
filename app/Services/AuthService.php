<?php

namespace App\Services;

use App\Helpers\Constant;
use App\Models\DeviceToken;
use App\Models\User;
// use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class AuthService
{
    public function create($request){
        $request->merge([
            'password' => bcrypt($request->password),
            'role_id'  => Constant::ROLE_USER
        ]);

        $user = User::create($request->all());
        return customResponse(true, "User created successfully.", 201, $user);
    }

    public function login($request){
        if(auth()->attempt([
            'email' => $request->email,
            'password' => $request->password
        ])){
            if(auth()->user()->status == Constant::INACTIVE){
                return customResponse(false, "Your account is not active.", 422);
                // throw new AccessDeniedHttpException("Your account is not active.");
            }
            if(auth()->user()->role_id == Constant::ROLE_ADMIN){
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
            'email'    => $request->email,
            'password' => $request->password,
            'role_id'  => Constant::ROLE_ADMIN
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