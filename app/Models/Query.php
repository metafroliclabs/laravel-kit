<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Query extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'user_type',
        'subject',
        'message'
    ];
}
