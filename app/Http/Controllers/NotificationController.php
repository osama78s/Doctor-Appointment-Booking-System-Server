<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Traits\ApiTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    use ApiTrait;

    public function index()
    {
        $user = Auth::user();
        $notifications = Notification::with('user')->where('user_id', $user->id)->get();
        return $this->data(compact('notifications'));
    }

}
