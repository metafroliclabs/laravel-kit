<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\BadRequestException;
use App\Http\Controllers\MainController;
use App\Http\Requests\Common\ForgetPasswordRequest;
use App\Http\Requests\Common\ResetPasswordRequest;
use App\Models\PasswordReset;
use App\Models\User;
use App\Notifications\ForgetPasswordNotification;
use Illuminate\Http\Request;

class ForgetPasswordController extends MainController
{
    protected $user;
    protected $passwordReset;

    public function __construct(User $user, PasswordReset $passwordReset)
    {
        parent::__construct();
        $this->user = $user;
        $this->passwordReset = $passwordReset;
    }

    public function forgot(ForgetPasswordRequest $request)
    {
        $user    = $this->user->getUserByEmail($request->email);
        $delete  = $this->passwordReset->email($request->email)->delete();
        $code    = $this->passwordReset->getCode();
        $message = $this->passwordReset->saveCode($request->email, $code);

        $user->notify(new ForgetPasswordNotification($code));
        return $this->response->successMessage("We have sent you a 6 digit code on your email!");
    }

    public function verify(Request $request)
    {
        $code = $this->passwordReset->token($request->code)->email($request->email)->first();
        if ($code) {
            return $this->response->successMessage("Email has been verified successfully.");
        }
        throw new BadRequestException("Invalid code, try again");
    }

    public function reset(ResetPasswordRequest $request)
    {
        // $data     = $this->passwordReset->email($request->email)->first();
        $response = $this->user->resetPassword($request);
        $delete   = $this->passwordReset->email($request->email)->delete();
        return $this->response->successMessage("Password reset successfully.");
    }
}
