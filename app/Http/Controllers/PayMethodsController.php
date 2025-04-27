<?php

namespace App\Http\Controllers;

use App\Events\PusherEvent;
use App\Models\Appointment;
use App\Models\Notification;
use App\Models\Reservation;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use App\Traits\ApiTrait;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Illuminate\Support\Facades\Session as StripeSession;


class PayMethodsController extends Controller
{
    use ApiTrait;

    public function createPaypalAction(Request $request, $id)
    {
        $payment_method = $request->query('payment_method', 'cache');

        if (!$payment_method) {
            return $this->errorsMessage(['error' => 'Payment Method Is Required']);
        }

        $reservation = Reservation::where('id', $id)->with('feese')->first();
        $reservation->payment_method = $payment_method;
        $reservation->save();

        if ($reservation->payment_method == 'paypal') {
            $provider = new PayPalClient;
            $provider->setApiCredentials(config('paypal'));
            $paypal_token = $provider->getAccessToken();

            $response = $provider->createOrder(
                [
                    "intent" => "CAPTURE", // Corrected from "ntent" to "intent"
                    "application_context" =>
                    [
                        // frontend_Url
                        "return_url" => url('https://clinic-client-m.vercel.app/my-appointments'),
                        "cancel_url" => url('https://clinic-client-m.vercel.app/'),
                    ],
                    "purchase_units" =>
                    [
                        [
                            "amount" =>
                            [
                                "currency_code" => "USD",
                                "value" => $reservation->feese->price, // Ensure this value is a string
                            ],
                            "custom_id" => $reservation->id,
                        ]
                    ]
                ]
            );
            // return $response;
            if (isset($response['id']) && $response['id'] != null) {
                foreach ($response['links'] as $link) {
                    if ($link['rel'] === 'approve') {
                        return $this->data([
                            'status' => 'success',
                            'order_id' => $response['id'],
                            'approval_url' => $link['href'],
                        ]);
                    }
                }
            }
        }
    }

    public function success_paypal_payment(Request $request)
    {
        $user = Auth::user();

        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $paypalToken = $provider->getAccessToken();
        $response = $provider->capturePaymentOrder($request->query('token'));

        if (isset($response['purchase_units'][0]['payments']['captures'][0]['custom_id'])) {
            $reservation_id = $response['purchase_units'][0]['payments']['captures'][0]['custom_id'];
            $reservation = Reservation::where('id', $reservation_id)->with('appointment')->first();
            if (!$reservation) {
                return response()->json(['error' => 'Reservation not found'], 404);
            }

            $reservation->is_paid = 'paid';
            $reservation->transaction_id = $response['id'];
            $reservation->save();

            $reservation->appointment->status = 'active';
            $reservation->appointment->save();

            event(new PusherEvent("$user->first_name  $user->last_name has made reservation", $reservation->doctor_id));

            Notification::create([
                'message' => "$user->first_name  $user->last_name has made reservation",
                'user_id' => $user->id,
                'doctor_id' => $reservation->doctor_id
            ]);

            return response()->json([
                'message' => 'Payment successful',
                'status' => $response['status']
            ], 200);
        } else {
            return response()->json(['error' => 'custom_id not found in PayPal response'], 400);
        }
    }


    public function createStripeAction(Request $request)
    {
        $request->validate([
            'reservation_id' => 'required|integer'
        ]);

        $reservation = Reservation::where('id', $request->reservation_id)->with('feese')->first();

        Stripe::setApiKey(config('services.stripe.secret'));

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => 'Reservation'
                    ],
                    'unit_amount' => $reservation->feese->price * 100, 
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => 'https://clinic-client-m.vercel.app/my-appointments?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => 'https://clinic-client-m.vercel.app/cancel',
        ]);

        $reservation->transaction_id = $session->id;
        $reservation->payment_method = 'stripe';
        $reservation->save();

        return response()->json(['sessionId' => $session->id]);
    }

    public function successStripeAction(Request $request)
    {
        $user = Auth::user();

        // انا حافظه ف ال services => config
        Stripe::setApiKey(config('services.stripe.secret'));

        // استرجاع session_id من الطلب
        $session_id = $request->query('session_id');

        if (!$session_id) {
            return response()->json(['message' => 'Transaction ID not found'], 400);
        }

        // جلب تفاصيل الجلسة من Stripe
        $session = Session::retrieve($session_id);


        // البحث عن الحجز بناءً على transaction_id
        $reservation = Reservation::with('appointment')->where('transaction_id', $session_id)->first();

        if (!$reservation) {
            return response()->json(['message' => 'Reservation not found'], 404);
        }

        // التحقق من حالة الدفع في Stripe
        if ($session->payment_status === 'paid') {
            // تحديث الحجز كمدفوع
            $reservation->is_paid = 'paid';
            $reservation->save();

            // تحديث حالة الموعد
            $reservation->appointment->status = 'active';
            $reservation->appointment->save();


            event(new PusherEvent("$user->first_name  $user->last_name has made reservation", $reservation->doctor_id)); 
            
            Notification::create([
                'message' => "$user->first_name  $user->last_name has made reservation",
                'user_id' => $user->id,
                'doctor_id' => $reservation->doctor_id
            ]);

            return response()->json([
                'message' => 'Payment successful',
                'status' => 'COMPLETED',
            ], 200);
        } else {
            // إذا لم يتم الدفع، يتم حذف الحجز
            $reservation->delete();

            return response()->json([
                'message' => 'Payment failed, reservation deleted',
                'status' => 'FAILED',
            ], 400);
        }
    }

    public function createCacheAction($id)
    {
        $user = Auth::user();

        $reservation = Reservation::with('appointment')->where('id', $id)->first();
        $reservation->payment_method = 'cache';
        $reservation->save();
        $reservation->appointment->status = 'active';
        $reservation->appointment->save();
        
        event(new PusherEvent("$user->first_name  $user->last_name has made reservation", $reservation->doctor_id)); 
        
        Notification::create([
            'message' => "$user->first_name  $user->last_name has made reservation",
            'user_id' => $user->id,
            'doctor_id' => $reservation->doctor_id
        ]);

        return $this->data([
            'message' => 'Payment Successful! Your reservation is confirmed.',
            'status' => 'COMPLETED',
        ]);
    }

}
