<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Test extends Model
{
    use HasFactory,HasTranslations;

    protected $guarded = [];
    public $translatable = ['name','descreption'];
    protected $table = 'tests';
}
