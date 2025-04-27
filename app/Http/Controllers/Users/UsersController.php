<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\Day;
use App\Models\Reservation;
use App\Models\Review;
use App\Models\Specialization;
use App\Traits\ApiTrait;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserDocumentation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use function PHPSTORM_META\map;



class UsersController extends Controller
{
    use ApiTrait;

    public function getDoctors(Request $request)
    {
        $today = Carbon::today()->toDateString();
        $tomorrow = Carbon::tomorrow()->toDateString();

        $name = $request->query('name');
        $specialization_id = $request->query('specialization_id');
        $gender = $request->query('gender');
        $availability = $request->query('availability');
        $salary = $request->query('salary');
        $age = $request->query('age');

        $query = User::where('role', 'doctor')
            ->when($name, function ($query, $name) {
                return $query->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$name%"]);
            })
            ->when($specialization_id, function ($query, $specialization_id) {
                return $query->where('specialization_id', $specialization_id);
            })
            ->when($gender, function ($query, $gender) {
                return $gender === 'all' ? $query : $query->where('gender', $gender);
            })
            ->when($availability, function ($doctor) use ($availability, $today, $tomorrow) {
                $doctor->whereHas('appointments.days', function ($day) use ($availability, $today, $tomorrow) {
                    if ($availability == 'today') {
                        $day->where('date', $today);
                    } elseif ($availability == 'tomorrow') {
                        $day->where('date', $tomorrow);
                    }
                });
            })
            ->when($salary, function ($doctor, $salary) {
                $doctor->whereHas('feeses', function ($fees) use ($salary) {
                    if ($salary < 50) {
                        return $fees->where('price', '<', $salary);
                    } elseif ($salary >= 50 && $salary <= 100) {
                        return $fees->whereBetween('price', [50, 100]);
                    } elseif ($salary >= 100 && $salary <= 200) {
                        return $fees->whereBetween('price', [100, 200]);
                    } elseif ($salary >= 200 && $salary <= 300) {
                        return $fees->whereBetween('price', [200, 300]);
                    } elseif ($salary > 300) {
                        return $fees->where('price', '>', 300);
                    }
                });
            })
            ->when($age, function ($query, $age) {
                if ($age < 30) {
                    return $query->where('age', '<', 30);
                } elseif ($age >= 30 && $age <= 50) {
                    return $query->whereBetween('age', [30, 50]);
                } elseif ($age > 50) {
                    return $query->where('age', '>', 50);
                }
            });

        $page = $name ? 1 : request('page', 1);
        $doctors = $query->paginate(5, ['*'], 'page', $page);

        $doctors->transform(function ($doctor) use ($today) {
            $doctor->image_url = asset('images/users/' . $doctor->image);
            $doctor->specialization_name = $doctor->specialization ? $doctor->specialization->name_en : '';
            $doctor->avg_rating = round($doctor->reviews_doctors()->avg('rate'));
            $doctor->reservation_count = $doctor->reservations_users->where('status', 'complete')->count();
            $doctor->feeses;

            $doctor->days = Day::where('date', '>=', $today)->with(['appointments' => function ($appointment) use ($doctor) {
                $appointment->where('user_id', $doctor->id)->where('status', 'un_active');
            }])->orderBy('date')->get()->map(function ($day) {
                $day->day = $day->day . ' ' . date('d/m/Y', strtotime($day->date));
                return $day;
            });

            unset($doctor->specialization, $doctor->reservations_users);
            return $doctor;
        });

        $totalPages = $doctors->lastPage();

        return $this->data(compact('doctors', 'totalPages'));
    }

    public function showDoctor($id)
    {

        $doctor = User::with(['feeses'])->findOrFail($id);
        $doctor->avg_rating = round($doctor->reviews_doctors->avg('rate'));
        $doctor->reservation_count = $doctor->reservations_doctor->whereIn('status', ['complete', 'finished'])->count();
        $doctor->specialization_name = $doctor->specialization ?  $doctor->specialization->name_en : '';
        $doctor->image_url = asset('images/users/' . $doctor->image);
        $doctor->days = Day::with(['appointments' => function ($query) use ($doctor) {
            $query->where('user_id', $doctor->id)->where('status', 'un_active');
        }])->get()->map(function ($day) {
            $day->day .= ' ' . date('m/d', strtotime($day->date));
            return $day;
        });
        $doctor->reviews = $doctor->reviews_doctors()->with('user')->paginate(5)->through(function ($review) {
            $review->user->image_url = asset('images/users/' . $review->user->image);
            return $review;
        });
        $totalPages = $doctor->reviews->lastPage();
        unset($doctor->reservations_doctor, $doctor->specialization, $doctor->reviews_doctors);
        return $this->data(compact('doctor', 'totalPages'));
    }


    public function getTopDoctorsBySpecialization()
    {
        $doctors = User::where('role', 'doctor')->with(['reviews_doctors', 'specialization'])->get();

        $topDoctors = $doctors->groupBy('specialization_id')->map(function ($doctors) {
            $bestDoctor = $doctors->sortByDesc(function ($doctor) {
                return round($doctor->reviews_doctors->avg('rate'));
            })->first();

            return [
                'id' => $bestDoctor->id,
                'first_name' => $bestDoctor->first_name,
                'last_name' => $bestDoctor->last_name,
                'average_rating' => round($bestDoctor->reviews_doctors->avg('rate')),
                'specialization' => $bestDoctor->specialization->name_en ?? '',
                'image_url' => asset('images/users/' . $bestDoctor->image),
            ];
            })->values();

        return $this->data(['topDoctors' => $topDoctors]);
    }

    public function getDocumentations()
    {
        $user_id = Auth::user()->id;
        $documentations = UserDocumentation::where('user_id', $user_id)->with('userDocsImages')->with('doctor')->get();
        return $this->data(compact('documentations'));
    }

    public function getReservations()
    {
        $user_id = Auth::user()->id;
        $reservations = Reservation::where('user_id', $user_id)->where('payment_method', '!=', null)->with(['doctor.specialization', 'appointment', 'feese'])->get();
        return $this->data(compact('reservations'));
    }

    public function ourHappyClient()
    {
        $reviews = Review::where('rate', '5')->with('user:id,first_name,last_name,image')
            ->orderBy('id')
            ->get()
            ->unique(fn($review) => $review->user_id . '-' . $review->doctor_id)
            ->take(6)
            ->map(function ($review) {
                $review->user->image_url = asset('images/users/' . $review->user->image);
                return $review;
            });

        return $this->data(compact('reviews'));
    }

    // when user make review on the doctor 
    public function reservationStatus($id)
    {
        $reservation_status = Reservation::where('doctor_id', $id)->where('user_id', Auth::id())->get();
        $status = $reservation_status->whereIn('status', ['complete', 'finished'])->first();
        if ($status) {
            $success = true;
        } else {
            $success = false;
        }
        return $this->data(compact('success'));
    }
}
