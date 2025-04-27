<?php

namespace App\Http\Controllers\Specialization;

use App\Http\Controllers\Controller;
use App\Http\Requests\Specialization\StoreRequest;
use App\Models\Specialization;
use App\Models\User;
use App\Traits\ApiTrait;
use Illuminate\Support\Facades\Auth;
use League\CommonMark\Util\SpecReader;

class SpecializationController extends Controller
{
    use ApiTrait;

    public function index(){
        $user_id = Auth::user()->id;
        $user = User::find($user_id);
        $specialization = Specialization::all();
        if($specialization->isEmpty()){
            return $this->successMessage('No Data Here');
        }
        return $this->data(compact('specialization'));
    }

    public function store(StoreRequest $request){
        $user_id = Auth::user()->id;
        $data = Specialization::create([
            'name_en' => $request->name_en,
            'name_ar' => $request->name_ar,
            'created_by' => $user_id
        ]);
        return $this->successMessage('Created Successfully');
    }

    public function delete($id){
        $specialization = Specialization::find($id);
        $specialization->is_deleted = "0";
        $specialization->save();
        return $this->successMessage('Deleted Successfully');
    }
    
}
