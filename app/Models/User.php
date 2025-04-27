<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Ramsey\Uuid\FeatureSet;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute(){
        return asset('images/users/' . $this->image);
    }

    public function specialization(){
        return $this->belongsTo(Specialization::class);
    }

    public function feeses(){
        return $this->hasMany(Feese::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
    
    public function reviews_users()
    {
        return $this->hasMany(Review::class,'user_id');
    }

    public function reviews_doctors()
    {
        return $this->hasMany(Review::class,'doctor_id');
    }


    public function reservations_users()
    {
        return $this->hasMany(Reservation::class,'user_id');
    }

    public function reservations_doctor()
    {
        return $this->hasMany(Reservation::class,'doctor_id');
    }


    public function user_docs()
    {
        return $this->hasMany(UserDocumentation::class);
    }

    public function doctor_docs()
    {
        return $this->hasMany(UserDocumentation::class, 'doctor_id');
    }

    public function contact() {
        return $this->hasMany(Contact::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}