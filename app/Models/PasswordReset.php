<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    use HasFactory;

    protected $table = "password_reset_tokens";

    public function getCode(){
        return rand(100001, 999999);
    }

    public function saveCode($email, $code){
        $this->updateOrInsert([
            'email' => $email,
        ],[
            'email' => $email,
            'token' => $code,
            'created_at' => now(),
        ]);

        return customResponse(true, "We have sent you a 6 digit code on your email!");
    }

    // SCOPES
    public function scopeToken($query, $token){
        return $query->where('token', $token);
    }

    public function scopeEmail($query, $email){
        return $query->where('email', $email);
    }
}
