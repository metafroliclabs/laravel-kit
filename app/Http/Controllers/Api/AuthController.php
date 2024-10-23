<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Common\LoginRequest;
use App\Http\Requests\Common\SignupRequest;
use App\Models\DeviceToken;
use App\Services\Core\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function signup(SignupRequest $request)
    {
        if ($request->image) {
            $data = uploadFile($request->image);
            $request->merge(['avatar' => $data['data']]);
        }

        $user = $this->authService->create($request);
        return apiResponse(true, "Profile has been created successfully.");
    }

    public function login(LoginRequest $request)
    {
        $data = $this->authService->login($request);
        return apiResponse(true, "login successfull", 200, $data);
    }

    public function login_admin(LoginRequest $request)
    {
        $data = $this->authService->login_admin($request);
        return apiResponse(true, "login successfull", 200, $data);
    }

    public function logout(Request $request)
    {
        if ($request->device_id) {
            DeviceToken::where('user_id', auth()->id())->where('device_id', $request->device_id)->delete();
        }
        $request->user()->currentAccessToken()->delete();
        return apiResponse(true, "logout successfully!");
    }
}
