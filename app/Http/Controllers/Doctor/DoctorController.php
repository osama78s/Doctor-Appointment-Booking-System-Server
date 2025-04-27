<?php

namespace App\Http\Controllers\Doctor;

use App\Events\PusherEvent;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Day;
use App\Models\Notification;
use App\Models\Reservation;
use App\Models\Review;
use App\Models\User;
use App\Models\UserDocumentation;
use App\Traits\ApiTrait;
use App\Traits\Model;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DoctorController extends Controller
{
    use ApiTrait, Model;

    // Index Dashoard 

    public function todayReservations()
    {
        $today = Carbon::today()->toDateString();
        $doctor_id = Auth::user()->id;

        $reservations = Reservation::where('doctor_id', $doctor_id)
            ->with(['appointment.days', 'user'])
            ->where('status', '!=', 'finished')
            ->get();

        $today_reservations = $reservations->filter(function ($reservation) use ($today) {
            return optional($reservation->appointment->days)->date == $today;
        });

        $today_reservations->map(function ($reservation) {
            $reservation->user->image_url = asset('images/users/' . $reservation->user->image);
            return $reservation;
        });

        $weekly_earnings = Day::with(['reservations' => function ($query) use ($doctor_id) {
            $query->where('doctor_id', $doctor_id)->where('status', '!=', 'finished');
        }, 'reservations.feese'])
        ->orderBy('date', 'asc')
        ->get()
        ->mapWithKeys(function ($day) {
            return [$day->day => ['total_price' => $day->reservations->sum(fn($reservation) => $reservation->feese?->price ?? 0)]];
        });

        $total_users = $reservations->pluck('user_id')->unique()->count();

        $total_users_today = $today_reservations->pluck('user_id')->unique()->count();

        $total_users_first_reservation = $reservations->where('review', '0')->count();

        $total_review = Review::where('doctor_id', $doctor_id)->count();

        $weakly_appointments = Day::withCount(['appointments' => function ($appointment) use ($doctor_id) {
            $appointment->where('user_id', $doctor_id)->where('status', '!=', 'finished');
        }])->get();

        $total_weakly_appointments = $weakly_appointments->sum('appointments_count');


        return $this->data(compact('today_reservations', 'total_review', 'total_users', 'total_users_today', 'total_users_first_reservation', 'weakly_appointments', 'total_weakly_appointments', 'weekly_earnings'));
    }

    // Index Dashoard && Reservations

    public function completeReservations($id)
    {
        $reservation = Reservation::find($id);
        if ($reservation->status === 'pendding') {
            $reservation->status = 'complete';
            if($reservation->payment_method == 'cache' && $reservation->is_paid == 'not_paid'){
                $reservation->is_paid = 'paid';
            }
            $reservation->save();
            return $this->successMessage('Completed Successfully');
        }
    }

    public function cancelReservations($id)
    {
        $reservation = Reservation::find($id);
        if ($reservation->status === 'pendding') {
            $reservation->status = 'cancel';
            $reservation->save();
            return $this->successMessage('Canceled Successfully');
        }
    }

    // Avilability Dashboard 

    public function allAppointements()
    {
        $doctor_id = Auth::user()->id;
        $weakly_appointments = Day::with(['appointments' => function ($query) use ($doctor_id) {
            $query->where('user_id', $doctor_id)->where('status', '!=', 'finished');
        }])->orderBy('date')->get();
        $days = Day::all();
        return $this->data(compact('weakly_appointments', 'days'));
    }


    // Reservation Dashboard

    public function getAllReservations()
    {
        $doctor_id = Auth::user()->id;
        $weakly_reservations = Reservation::where('doctor_id', $doctor_id)
        ->where('status', '!=', 'finished')->with(['user', 'day'])->get();
        $weakly_reservations->map(function ($reservation) {
            $reservation->user->image_url = asset('images/users/' . $reservation->user->image);
            return $reservation;
        });
        return $this->data(compact('weakly_reservations'));
    }


    // Users Dashboard
    public function getUsers()
    {
        $doctor_id = Auth::user()->id;

        $users = Reservation::where('doctor_id', $doctor_id)
            ->get()
            ->groupBy('user_id') 
            ->map(function ($reservations) {
                return $reservations->first()->user; 
            })
            ->values(); 

        return $this->data(['users' => $users]);
    }

    public function getUser($id)
    {
        $doctor_id = Auth::user()->id;
    
        $user = User::with([
            'reviews_users'=> function($query) use ($doctor_id) {
                $query->where('doctor_id', $doctor_id);
            }, 
            'reservations_users' => function($query) use ($doctor_id) {
                $query->where('doctor_id', $doctor_id);
            }, 
            'user_docs.userDocsImages'
        ])->find($id);

        $user->image_url = asset('images/users/'.$user->image);
    
        return $this->data(compact('user'));
    }

    
    public function getAllReviews()
    {
        $doctor_id = Auth::user()->id;
        $reviews = Review::where('doctor_id', $doctor_id)->get();
        return $reviews;
    }

    // =======================================================

    public function updateDocs(Request $request, $id)
    {
        $documentation = UserDocumentation::with('userDocsImages')->find($id);
    
        if (!$documentation) {
            return $this->errorsMessage(['error' => 'Documentation Not Found']);
        }
    
        DB::transaction(function () use ($request, $documentation) {
            $documentation->update([
                'type' => $request->type,
                'desc' => $request->desc,
            ]);
    
            if ($request->hasFile('image')) {
                $this->deleteDocsImages($documentation);
                $this->storeImages($request, $documentation);
            }
        });
    
        return $this->successMessage('Updated Successfully');
    }
    
    public function deleteDocs($id){
        $doctor_id = Auth::user()->id;
        $documentation = UserDocumentation::where('id', $id)->where('doctor_id', $doctor_id)->first();
        $documentation->delete();
        return $this->successMessage('Deleted Successfully');
    }

    public function storeDocs(Request $request)
    {
        $doctor = Auth::user();
        
        $request->validate([
            'type' => 'required|string',
            'desc' => 'required|string',
            'user_id' => 'required|integer'
        ]);

        $documentation = UserDocumentation::create([
            'type' => $request->type,
            'desc' => $request->desc,
            'doctor_id' => $doctor->id,
            'user_id' => $request->user_id
        ]);

        if ($request->hasFile('image')) {
            $this->storeImages($request, $documentation);
        }

        event(new PusherEvent("$doctor->first_name $doctor->last_name has stored a report", $request->user_id));

        Notification::create([
            'message' => "$doctor->first_name $doctor->last_name has stored report",
            'user_id' => $request->user_id,
            'doctor_id' => $doctor->id
        ]);

        return $this->successMessage('Created Successfully', 201);
    }
}
