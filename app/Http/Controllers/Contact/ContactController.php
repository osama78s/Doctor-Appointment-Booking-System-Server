<?php

namespace App\Http\Controllers\Contact;

use App\Http\Controllers\Controller;
use App\Http\Requests\Contact\StoreRequest;
use App\Mail\ContactMail;
use App\Models\Contact;
use App\Models\User;
use App\Traits\ApiTrait;
use Hamcrest\Type\IsString;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    use ApiTrait;
    public function store(StoreRequest $request){
        $user = Auth::user();
        $admin = User::where('role', 'admin')->where('email', 'osamasaif242@gmail.com')->first();
        Mail::to($admin)->send(new ContactMail(
            $user->first_name,
            $user->last_name,
            $user->email,
            $request->message,
        ));
        return $this->successMessage('Send Mail Successfully');
    }
}
