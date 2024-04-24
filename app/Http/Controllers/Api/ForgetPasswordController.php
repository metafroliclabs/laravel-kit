<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ForgetPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Mail\PasswordResetCodeMail;
use App\Models\PasswordReset;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ForgetPasswordController extends Controller
{
    protected $user;
    protected $passwordReset;

    public function __construct(User $user, PasswordReset $passwordReset){
        $this->user = $user;
        $this->passwordReset = $passwordReset;
    }

    public function forgot(ForgetPasswordRequest $request){
        try{
            $user    = $this->user->getUserByEmail($request->email);
            $delete  = $this->passwordReset->email($request->email)->delete();
            $code    = $this->passwordReset->getCode();
            $message = $this->passwordReset->saveCode($request->email, $code);
            
            Mail::to($request->email)->send(new PasswordResetCodeMail($user, $code));
            return apiResponse(...$message);
        }catch(Exception $e){
            return apiResponse(false, $e->getMessage(), 500);
        }
    }

    public function verify(Request $request){
        try{
            $code = $this->passwordReset->token($request->code)->email($request->email)->first();
            if($code)
                return apiResponse(true, 'Code has been verified successfully.');
            else
                return apiResponse(false, 'Invalid code, please try again.', 422);
            
        }catch(Exception $e){
            return apiResponse(false, $e->getMessage(), 500);
        } 
    }

    public function reset(ResetPasswordRequest $request){
        try{
            $data = $this->passwordReset->email($request->email)->first();
            if(!$data)
                return apiResponse(false, 'Email is invalid.', 409);
            
            $response = $this->user->resetPassword($request);
            $this->passwordReset->token($data->token)->email($request->email)->delete();
            return apiResponse(...$response);
        }catch(Exception $e){
            return apiResponse(false, $e->getMessage(), 500);
        }        
    }
}
