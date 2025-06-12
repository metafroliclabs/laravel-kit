<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\MainController;
use App\Http\Requests\Common\LoginRequest;
use App\Http\Requests\Common\SignupRequest;
use App\Models\DeviceToken;
use App\Services\Core\AuthService;
use Illuminate\Http\Request;

class AuthController extends MainController
{
    public $authService;

    public function __construct(AuthService $authService)
    {
        parent::__construct();
        $this->authService = $authService;
    }

    public function signup(SignupRequest $request)
    {
        if ($request->image) {
            $data = uploadFile($request->image);
            $request->merge(['avatar' => $data['data']]);
        }

        $user = $this->authService->create($request);
        return $this->response->successMessage("Profile has been created successfully.");
    }

    public function login(LoginRequest $request)
    {
        $data = $this->authService->login($request);
        return $this->response->success($data);
    }

    public function login_admin(LoginRequest $request)
    {
        $data = $this->authService->login_admin($request);
        return $this->response->success($data);
    }

    public function logout(Request $request)
    {
        // if ($request->device_id) {
        //     DeviceToken::where('user_id', auth()->id())->where('device_id', $request->device_id)->delete();
        // }
        $request->user()->currentAccessToken()->delete();
        return $this->response->successMessage("logout successfully!");
    }
}
