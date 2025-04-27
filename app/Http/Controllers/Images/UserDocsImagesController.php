<?php

namespace App\Http\Controllers\Images;

use App\Traits\ApiTrait;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class UserDocsImagesController extends Controller
{
    use ApiTrait;
    public function index()
    {
        $user = Auth::user();

        if ($user->role == 'admin') 
        {
            $docs = UserDocumentation::all();

        } elseif($user->role == 'user') 
        {
           $docs = User::find($user->id)->user_docs;
        }else
        {
            // doctor 
        }


        if ($docs->isEmpty()) 
        {
            return $this->errorsMessage(['error' => 'No Data Here']);
        }

        return $this->data(compact('docs'));
    }
}
