<?php

use App\Http\Controllers\Admin\AdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\testController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\VerifyController;
use App\Http\Controllers\Users\UsersController;
use App\Http\Controllers\Doctor\DoctorController;
use App\Http\Controllers\Feeses\FeesesController;
use App\Http\Controllers\SpecializionsController;
use App\Http\Controllers\Images\UserDocsImagesController;
use App\Http\Controllers\Appointments\AppointmentsController;
use App\Http\Controllers\Contact\ContactController;
use App\Http\Controllers\Days\DaysController;
use App\Http\Controllers\Reservations\ReservationsController;
use App\Http\Controllers\Documents\UserDocumentationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PayMethodsController;
use App\Http\Controllers\Reviews\ReviewsController;
use App\Http\Controllers\Specialization\SpecializationController;
use App\Models\Contact;
use App\Models\User;
use App\Notifications\NewMessageNotification;
use Illuminate\Support\Facades\Auth;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/auth/google', [AuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);

Route::prefix('users')->group(function () {
    
    Route::post('/check-forget-password', [VerifyController::class, 'verifyForgetPassword']);
    Route::post('/forget-password', [AuthController::class, 'forgetPassword']);

    Route::prefix('get-doctors')->group(function () {
        Route::get('/', [UsersController::class, 'getDoctors']);
        Route::get('/best-specialization-doctor', [UsersController::class, 'getTopDoctorsBySpecialization']);
        Route::get('/show/{id}', [UsersController::class, 'showDoctor']);
        Route::get('/reservation-status/{id}', [UsersController::class, 'reservationStatus'])->middleware('auth:sanctum');
        Route::get('/reviews-client', [UsersController::class, 'ourHappyClient']);
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::get('/logout', [AuthController::class, 'logout']);
        Route::post('/profile', [AuthController::class, 'profile']);
        Route::get('/send-code', [VerifyController::class, 'sendCode']);
        Route::post('/check-code', [VerifyController::class, 'checkCode']);
        Route::post('/contact', [ContactController::class, 'store']);
        Route::get('/documentation', [UsersController::class, 'getDocumentations']);
        Route::get('/reservations', [UsersController::class, 'getReservations']);
    });

});

Route::get('/days', [DaysController::class, 'updateDate']);
Route::get('/specialization', [SpecializationController::class, 'index']);

Route::prefix('/')->middleware('auth:sanctum')->group(function () {

    // Route::prefix('specialization')->group(function () {
    //     Route::post('/store', [SpecializationController::class, 'store']);
    //     Route::delete('/{id}', [SpecializationController::class, 'delete']);
    // });

    Route::prefix('user_documentations')->group(function () {
        Route::get('/', [UserDocumentationController::class, 'index']);
        Route::get('/show/{id}', [UserDocumentationController::class, 'show']);
        Route::post('/store', [UserDocumentationController::class, 'store'])->middleware('check.Get_Doctor_User');
        Route::post('/update/{id}', [UserDocumentationController::class, 'update']);
        Route::delete('/delete/{id}', [UserDocumentationController::class, 'delete']);
        Route::delete('/deleteImage/{id}', [UserDocumentationController::class, 'deleteImage']);
    });

    Route::prefix('reservations')->group(function () {
        Route::post('/store', [ReservationsController::class, 'store']);
        Route::post('/cancel/{id}', [ReservationsController::class, 'cancel']);
    });

    // remove this Middleware 
    Route::prefix('reviews')->group(function () {
        Route::post('/store', [ReviewsController::class, 'store']);
        Route::post('/update/{id}', [ReviewsController::class, 'update']);
        Route::delete('/delete/{id}', [ReviewsController::class, 'delete']);
    });
    
});

// DOCTORS
Route::prefix('doctors')->middleware('auth:sanctum', 'check.Get_doctor')->group(function () {

    Route::prefix('appointments')->group(function () {
        Route::get('/all-appointements', [DoctorController::class, 'allAppointements']);
        Route::post('/store', [AppointmentsController::class, 'store']);
        Route::post('/update/{id}', [AppointmentsController::class, 'update']);
        Route::delete('/delete/{id}', [AppointmentsController::class, 'delete']);
    });

    Route::prefix('reservations')->group(function () {
        Route::get('/', [DoctorController::class, 'getAllReservations']);
        Route::get('/today-reservations', [DoctorController::class, 'todayReservations']);
        Route::get('/complete_reservations/{id}', [DoctorController::class, 'completeReservations']);
        Route::get('/cancel_reservations/{id}', [DoctorController::class, 'cancelReservations']);
    });

    Route::prefix('users')->group(function () {
        Route::get('/', [DoctorController::class, 'getUsers']);
        Route::get('/{id}', [DoctorController::class, 'getUser']);
    });

    Route::prefix('documentations')->group(function () {
        Route::post('/store', [DoctorController::class, 'storeDocs']);
        Route::post('/update/{id}', [DoctorController::class, 'updateDocs']);
        Route::delete('/delete/{id}', [DoctorController::class, 'deleteDocs']);
    });

    Route::prefix('feeses')->group(function () {
        Route::get('/', [FeesesController::class, 'index']);
        Route::post('/store', [FeesesController::class, 'store']);
        Route::post('/{id}', [FeesesController::class, 'update']);
    });

    Route::prefix('reviews')->group(function () {
        Route::get('/', [DoctorController::class, 'getAllReviews']);
    });
});

// Admin 
Route::prefix('admin')->middleware(['auth:sanctum', 'check.Get_Admin'])->group(function () {

    Route::prefix('/')->group(function () {
        Route::post('/make/{id}', [AdminController::class, 'changeRole']);
        Route::get('/dashboard', [AdminController::class, 'allContentInDashboard']);
    });

    Route::prefix('/specializations')->group(function () {
        Route::get('/',[AdminController::class, 'getAllSpecializations']);
        Route::get('/active',[AdminController::class, 'getActiveSpecializations']);
        Route::post('/make/{id}', [AdminController::class, 'changeSpecializationStatus']);
        Route::post('/store', [AdminController::class, 'storeSpecialization']);
    });

    Route::prefix('users')->group(function () {
        Route::get('/', [AdminController::class, 'getUsers']);
        Route::get('/{id}', [AdminController::class, 'getUser']);
        Route::delete('/{id}', [AdminController::class, 'deleteUser']);
        Route::post('/store', [AdminController::class, 'storeUser']);
    });

    Route::prefix('doctors')->group(function () {
        Route::get('/', [AdminController::class, 'getDoctors']);
        Route::get('/{id}', [AdminController::class, 'getDoctor']);
        Route::post('/store', [AdminController::class, 'storeDoctor']);
    });

    Route::prefix('reviews')->group(function () {
        Route::delete('/{id}', [AdminController::class, 'deleteReview']);
    });
});

// Payment Getway
Route::prefix('cache')->group(function () {
    Route::get('/create/{id}', [PayMethodsController::class, 'createCacheAction']);
});

Route::prefix('stripe')->group(function () {
    Route::post('/create-checkout-session', [PayMethodsController::class, 'createStripeAction']);
    Route::get('/stripe_success', [PayMethodsController::class, 'successStripeAction']);
});

Route::prefix('paypal')->group(function () {
    Route::get('/create/{id}', [PayMethodsController::class, 'createPaypalAction']);
    Route::get('/success_paypal', [PayMethodsController::class, 'success_paypal_payment']);
});
