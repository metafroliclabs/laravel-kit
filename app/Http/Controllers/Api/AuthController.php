<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Constant;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\SignupRequest;
use App\Models\DeviceToken;
use App\Services\AuthService;
use Exception;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function signup(SignupRequest $request){
        try{
            if ($request->image) {
                $avatar = uploadFile($request->image);
                $request->merge(['avatar' => $avatar]);
            } else {
                $request->merge(['avatar' => Constant::DEFAULT_AVATAR]);
            }

            $response = $this->authService->create($request);
            return apiResponse(true, "Profile has been created successfully.");
        }
        catch(Exception $e){
            return apiResponse(false, $e->getMessage(), 500);
        }
    }

    public function login(LoginRequest $request){
        try{
            $response = $this->authService->login($request);
            return apiResponse(...$response);
        }catch(Exception $e){
            return apiResponse(false, $e->getMessage(), 500);
        }
    }

    public function login_admin(LoginRequest $request){
        try{
            $response = $this->authService->login_admin($request);
            return apiResponse(...$response);
        }catch(Exception $e){
            return apiResponse(false, $e->getMessage(), 500);
        }
    }

    public function logout(Request $request){
        try{
            if ($request->device_id) {
                DeviceToken::where('user_id', auth()->id())->where('device_id', $request->device_id)->delete();
            }
            $request->user()->currentAccessToken()->delete();
            return apiResponse(true, "logout successfully!");
        }catch(Exception $e){
            return apiResponse(false, $e->getMessage(), 500);
        }
    }
}
