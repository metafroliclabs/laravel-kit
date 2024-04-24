<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\SignupRequest;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function signup(SignupRequest $request){
        try{
            if ($request->image) {
                $avatar = uploadImage($request->image);
                $request->merge(['avatar' => $avatar]);
            } else {
                $request->merge(['avatar' => 'storage/default.png']);
            }

            $user = $this->user->add($request);
            return apiResponse(true, "Profile has been created successfully.");
        }
        catch(Exception $e){
            return apiResponse(false, $e->getMessage(), 500);
        }
    }

    public function login(LoginRequest $request){
        try{
            $response = $this->user->login($request);
            return apiResponse(...$response);
        }catch(Exception $e){
            return apiResponse(false, $e->getMessage(), 500);
        }
    }

    public function login_admin(LoginRequest $request){
        try{
            $response = $this->user->login_admin($request);
            return apiResponse(...$response);
        }catch(Exception $e){
            return apiResponse(false, $e->getMessage(), 500);
        }
    }

    public function logout(Request $request){
        try{
            $request->user()->currentAccessToken()->delete();
            return apiResponse(true, "logout successfully!");
        }catch(Exception $e){
            return apiResponse(false, $e->getMessage(), 500);
        }
    }
}
