<?php

namespace App\Models;

use App\Models\User;
use App\Models\Feese;
use App\Models\Appointment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reservation extends Model
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


    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function feese()
    {
        return $this->belongsTo(Feese::class);
    }

    public function day() {
        return $this->belongsTo(Day::class);
    }
}
