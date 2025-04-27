<?php

namespace App\Http\Controllers\Reservations;

use App\Models\User;
use App\Traits\ApiTrait;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reservations\CancelRequest;
use App\Http\Requests\Reservations\StoreReservation;
use App\Models\Appointment;
use App\Models\Day;
use App\Models\Feese;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Srmklive\PayPal\Services\PayPal as PayPalClient;


class ReservationsController extends Controller
{
    use ApiTrait;

    public function store(StoreReservation $request)
    {
        $day = Day::find($request->day_id);
        $today = Carbon::today()->toDateString();

        if ($day->date >= $today) {
            $response = DB::transaction(function () use ($request) {
                $user = Auth::user();
                $doctor_id = $request->doctor_id;
                $appointement = Appointment::find($request->appointment_id);
                $reservation = Reservation::where('user_id', $user->id)
                    ->where('doctor_id', $doctor_id)->where('is_paid', 'paid')->first();

                $reviewValue = !is_null($reservation) ? '1' : '0';
                $feeseCount = $reviewValue === '1' ? 'two' : 'one';
                $feese = Feese::where('count_review', $feeseCount)->firstOrFail();

                $reservation = Reservation::create([
                    'review' => $reviewValue,
                    'day_id' => $request->day_id,
                    'appointment_id' => $request->appointment_id,
                    'doctor_id' => $doctor_id,
                    'user_id' => $user->id,
                    'feese_id' => $feese->id
                ]);
                
                $reservation_id = $reservation->id;
                return $this->data(compact('reservation_id'), 'Created Successfully');
            });

            return $response;
        } else {
            return $this->errorsMessage([], 'You must select from today onwards and not a previous day.');
        }
    }


    public function cancel(CancelRequest $request, $id)
    {
        DB::transaction(function () use ($id, $request) {
            $reservation = Reservation::find($id);
            if ($reservation->status === 'pendding') {
                $reservation->status = 'cancel';
                $reservation->save();

                $appointement = Appointment::find($request->appointment_id);
                $appointement->status = 'un_active';
                $appointement->save();
                return $this->data(compact('reservation'));
            }
        });
    }
}
