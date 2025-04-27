<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDocsImage extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $appends = ['image_url']; 

    public function getImageUrlAttribute()
    {
        return asset('images/docs_images/' . $this->image);
    }
    
    public function user_doc()
    {
        return $this->belongsTo(UserDocumentation::class);
    }

}
