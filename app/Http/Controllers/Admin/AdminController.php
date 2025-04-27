<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Day;
use App\Models\Reservation;
use App\Models\Review;
use App\Models\Specialization;
use App\Models\User;
use App\Traits\ApiTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    use ApiTrait;

    public function storeUser(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:8',
            'gender' => 'nullable|string|in:M,F',
            'role' => 'required|string|in:admin,user,doctor'
        ]);

        $data['password'] = Hash::make($data['password']);
        User::create($data);

        return $this->successMessage('Created Successfully');
    }

    public function storeDoctor(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:8',
            'gender' => 'nullable|string|in:M,F',
            'role' => 'required|string|in:admin,user,doctor',
            'specialization_id' => 'required|integer'
        ]);

        $data['password'] = Hash::make($data['password']);
        User::create($data);

        return $this->successMessage('Created Successfully');
    }

    public function storeSpecialization(Request $request)
    {
        $data = $request->validate([
            'name_en' => 'required|string'
        ]);
        Specialization::create($data);
        return $this->successMessage('Created Successfully');
    }

    public function getActiveSpecializations(Request $request)
    {
        $specializations = Specialization::where('is_deleted', '1')->get();
        return $this->data(compact('specializations'));
    }

    public function getAllSpecializations(Request $request)
    {
        $specializations = Specialization::query()->when($request->name, function($q) use ($request) {
            $q->where('name_en', 'like', "%{$request->name}%");
        })->get();
        return $this->data(compact('specializations'));
    }

    public function changeSpecializationStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string'
        ]);
        $speicalist = Specialization::find($id);
        $speicalist->is_deleted = $request->status;
        $speicalist->save();
        return $this->successMessage('Updated Status Successfully');
    }

    public function changeRole(Request $request, $id)
    {
        $data = $request->validate([
            'role' => 'required|string|in:admin,user,doctor'
        ]);
        $user = User::find($id);
        $user->role = $data['role'];
        $user->save();
        return $this->successMessage('Updated Role Successfully');
    }

    public function getUsers(Request $request)
    {
        $users = User::where('role', 'user')
            ->when($request->name, function ($q) use ($request) {
                $q->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$request->name}%"]);
            })
            ->get();
        return $this->data(compact('users'));
    }

    public function getUser($id)
    {
        $user = User::where('id', $id)->with([
            'reservations_users',
            'reviews_users',
            'user_docs.userDocsImages'
        ])->first();
        return $this->data(compact('user'));
    }

    public function deleteUser($id)
    {
        $admin = Auth::user();
        User::where('id', $id)->where('email', '!=', 'osamasaif242@gmail.com')
            ->where('email', '!=', $admin->email)->delete();
        return $this->successMessage('Deleted Successfully');
    }

    public function getDoctors(Request $request)
    {
        $doctors = User::where('role', 'doctor')
            ->when($request->name, function ($q) use ($request) {
                $q->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$request->name}%"]);
            })
            ->get();
        return $this->data(compact('doctors'));
    }

    public function getDoctor($id)
    {
        $doctor = User::where('id', $id)->with([
            'reservations_doctor' => function ($q) {
                $q->where('status', '!=', 'finished');
            },
            'reviews_doctors',
            'doctor_docs.userDocsImages'
        ])->first();
        return $this->data(compact('doctor'));
    }

    public function deleteReview($id)
    {
        Review::findOrFail($id)->delete();
        return $this->successMessage('Deleted Successfully');
    }

    public function allContentInDashboard()
    {

        $admin = Auth::user();

        $users_count_of_reservations = Reservation::all()->groupBy('user_id')->map(function ($reservation) {
            return $reservation->first();
        })->count();

        $weakly_appointments = Day::withCount(['appointments' => function ($q) {
            $q->where('status', '!=', 'active');
        }])->get();

        $weekly_earnings = Day::with(['reservations' => function ($query) {
            $query->where('status', '!=', 'finished');
        }, 'reservations.feese'])
            ->orderBy('date', 'asc')
            ->get()
            ->mapWithKeys(function ($day) {
                return [$day->day => ['total_price' => $day->reservations->sum(fn($reservation) => $reservation->feese?->price ?? 0)]];
            });

        $users_count = User::where('role', 'user')->count();
        $doctors_count = User::where('role', 'doctor')->count();

        $today = Carbon::today()->toDateString();

        $today_reservations = Day::with(['reservations' => function ($reservation) {
            $reservation->where('status', '!=', 'finished')->where('payment_method', '!=',  null)->where('is_paid', 'paid');
        }, 'reservations.user', 'reservations.appointment', 'reservations.doctor'])->where('date', $today)->get();

        $admins = User::where('role', 'admin')->orderBy('first_name')->get();

        return $this->data(compact('users_count_of_reservations', 'weakly_appointments', 'weekly_earnings', 'users_count', 'doctors_count', 'today_reservations', 'admins'));
    }
}
