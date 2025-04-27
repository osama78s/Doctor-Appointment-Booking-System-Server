<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDocumentation extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function doctor()
    {
        return $this->belongsTo(User::class,'doctor_id');
    }

    public function userDocsImages()
    {
        return $this->hasMany(UserDocsImage::class,'user_documentations_id');
    }


    // public function doctor()
    // {
    //     return $this->belongsTo(User::class,'doctor_id');
    // }
}
