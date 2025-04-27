<?php

namespace App\Http\Controllers\Reviews;

use App\Events\PusherEvent;
use App\Models\Review;
use App\Traits\ApiTrait;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Reviews\ReviewsRequest;
use App\Models\Notification;
use App\Models\Reservation;

class ReviewsController extends Controller
{
    use ApiTrait;


    public function store(ReviewsRequest $request)
    {
        $user = Auth::user();
        $doctor_id = $request->doctor_id;

        $reservations = Reservation::where('user_id', $user->id)
            ->where('doctor_id', $doctor_id)
            ->get();

        $completedReservation = $reservations->whereIn('status', ['complete', 'finished'])->first();

        if ($completedReservation) {
            Review::create([
                'comment'   => $request->comment,
                'rate'      => $request->rate,
                'user_id'   => $user->id,
                'doctor_id' => $doctor_id,
            ]);

            event(new PusherEvent("$user->first_name $user->last_name has made review", $doctor_id));
            Notification::create([
                'message' => "$user->first_name $user->last_name has made review",
                'user_id' => $user->id,
                'doctor_id' => $doctor_id
            ]);

            return $this->successMessage('Created Successfully');
        }else {
            return $this->errorsMessage(['error' => 'You Must Have Or Complete a Reservation']);
        }
    }

    public function update(Request $request, $id)
    {
        $review = Review::find($id);
        $review->update([
            'comment' => $request->comment,
            'rate' => $request->rate
        ]);
        return $this->successMessage('Updated Successfully');
    }


    public function delete($id)
    {
        $user = Auth::user();

        $review = Review::find($id);
        $review->delete();

        event(new PusherEvent("$user->first_name $user->last_name has delete review", 0));

        return $this->successMessage('Deleted Successfully');
    }
}
