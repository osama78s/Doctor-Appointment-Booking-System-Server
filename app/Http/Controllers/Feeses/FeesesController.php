<?php

namespace App\Http\Controllers\Feeses;

use App\Http\Controllers\Controller;
use App\Http\Requests\Fesses\StoreRequest;
use App\Models\Feese;
use App\Models\User;
use App\Traits\ApiTrait;
use FFI;
use Illuminate\Support\Facades\Auth;

class FeesesController extends Controller
{
    use ApiTrait;

    public function index()
    {
        $user_id = Auth::user()->id;
        $feeses = User::find($user_id)->feeses;
        return $this->data(compact('feeses'));
    }

    public function store(StoreRequest $request){
        $user_id = Auth::user()->id;
        $feeses = User::find($user_id)->feeses;       

        if ($feeses && $feeses->count() < 2) 
        {
            Feese::create(
                [
                    'price'         => $request->price,
                    'count_review'  => $request->count_review,
                    'user_id'       => $user_id
                ]);

            return $this->successMessage('Created Successfully');
        }else
        {
            return $this->errorsMessage(['error' => 'You have 2 Feeses']);
        }
       
    }

    public function update(StoreRequest $request, $id){
        $fesse = Feese::find($id);
        $fesse->price           = $request->price;
        $fesse->count_review    = $request->count_review;
        $fesse->save();
        return $this->data(compact('fesse'));
    }

}
