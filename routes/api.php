<?php

use App\Http\Controllers\BusMovementController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\PassengerBoardingController;
use App\Http\Controllers\RouteController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserAuthController;

Route::fallback(function () {
    return response()->json(['message' => 'Page not found!'], 404);
});

Route::post('/register', [UserAuthController::class, 'register']);
Route::post('/login', [UserAuthController::class, 'login']);
Route::post('/email/verify', [UserAuthController::class, 'verifyEmail']);

Route::middleware('auth:sanctum')->group(function () {
    // Route::post('/complete-profile', [UserAuthController::class, 'completeProfile']);
    Route::get('/me', [UserAuthController::class, 'me']);
    Route::post('/change-password', [UserAuthController::class, 'changePassword']);
    Route::post('/logout', [UserAuthController::class, 'logout']);
    Route::post('/user/store-device-token', [UserAuthController::class, 'storeDeviceToken']);

    Route::post('/user/update-organization', [UserAuthController::class, 'updateOrganization']);
    Route::put('/user/profile', [UserAuthController::class, 'updateProfile']);
    Route::put('/user/location', [UserAuthController::class, 'updateLocation']);

    Route::get('/driver/user', [DriverController::class, 'getByUserId']);
    Route::get('/driver/buses', [DriverController::class, 'getDriverBuses']);
    Route::put('/driver', [DriverController::class, 'updateDriverLicense']);

    Route::get('/organizations', [OrganizationController::class, 'index']);
    Route::get('/organizations/driver', [OrganizationController::class, 'getDriverOrganizations']);

    Route::get('/bus-movements', [BusMovementController::class, 'index']);
    Route::post('/bus-movements', [BusMovementController::class, 'createTrip']);
    Route::get('/bus-movements/driver', [BusMovementController::class, 'getDriverTrips']);
    Route::get('/bus-movements/single', [BusMovementController::class, 'getSingleTrip']);
    Route::put('/bus-movements/status', [BusMovementController::class, 'triggerStatus']);

    Route::get('/passenger-boardings', [PassengerBoardingController::class, 'index']);
    Route::post('/passenger-boardings', [PassengerBoardingController::class, 'store']);
    Route::put('/passenger-boardings/status', [PassengerBoardingController::class, 'triggerStatus']);

    Route::get('/bus-routes', [RouteController::class, 'index']);
});

Route::post('/forget-password', [UserAuthController::class, 'forgetPassword'])
    ->name('password.reset');

Route::get('users', [UserAuthController::class, 'allUsers']);

// Route::post('/send-notification', [NotificationController::class, 'sendNotification']);
