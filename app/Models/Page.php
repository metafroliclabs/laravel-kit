<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'title',
        'photo',
        'description',
        'status',
    ];

    // Accessors
    public function getPhotoAttribute($value)
    {
        return $value ? asset($value) : null;
    }
}
