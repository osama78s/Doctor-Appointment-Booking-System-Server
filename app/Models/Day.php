<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Day extends Model
{
    use HasFactory;
    
    protected $guarded = [];

    public function appointments() {
        return $this->hasMany(Appointment::class);
    }

    public function reservations() {
        return $this->hasMany(Reservation::class);
    }

}
