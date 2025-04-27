<?php

namespace App\Http\Controllers\Days;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Day;
use App\Models\Reservation;
use App\Traits\ApiTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DaysController extends Controller
{
    use ApiTrait;

    public function updateDate()
    {
        $today = Carbon::now();
        $days = ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        
        foreach ($days as $day) {
            // إذا كان اليوم هو نفسه، خليه النهارده، غير كده هاته في الاسبوع ده
            if ($today->format('l') === $day) {
                $date = $today->toDateString();
            } else {
                $date = $today->copy()->next($day)->toDateString();
                
                Appointment::all()->map(function ($appointment) {
                    $appointment->update([
                        'status' => 'finished'
                    ]);
                    return $appointment;
                });
        
                Reservation::all()->map(function ($reservation) {
                    $reservation->update([
                        'status' => 'finished'
                    ]);
                    return $reservation;
                });
            }

            Day::where('day', $day)->update([
                'date' => $date
            ]);
        }

        return $this->successMessage('Days updated successfully');
    }
}
