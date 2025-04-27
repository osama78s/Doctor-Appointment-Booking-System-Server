<?php

namespace App\Http\Controllers\Appointments;

use App\Models\User;
use App\Traits\ApiTrait;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Appointments\AppointmentsRequest;
use App\Http\Requests\Appointments\UpdateRequest;

class AppointmentsController extends Controller
{
    use ApiTrait;

    public function store(AppointmentsRequest $request){
        $user_id = Auth::user()->id;
        Appointment::create([
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'user_id' => $user_id,
            'day_id' => $request->day_id
        ]);
        return $this->successMessage('Created Successfully');
    }

    public function update(AppointmentsRequest $request, $id){
        $appointment = Appointment::find($id);
        $appointment->start_time = $request->start_time;
        $appointment->end_time = $request->end_time;
        $appointment->day_id = $request->day_id;
        $appointment->save();
        return $this->successMessage('Updated Successfully');
    }
    
    public function delete($id){
        $appointment = Appointment::find($id);
        $appointment->delete();
        return $this->successMessage('Deleted Successfully');
    }

}
