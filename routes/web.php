<?php

use App\Http\Controllers\DemoController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\ParcelTrackingController;
use App\Http\Controllers\PaymentRecordController;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;
use Modules\TripManagement\Entities\TripRequest;
use Pusher\Pusher;
use Pusher\PusherException;
use Illuminate\Support\Facades\Auth;
use Modules\AdminModule\Http\Controllers\SendNotificationController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//yassin log


Route::get('/debug-user', function () {
    return response()->json([
        'user' => Auth::user(),
        'is_guest' => Auth::guest(),
        'session' => session()->all(),
    ]);
});


Route::group(['middleware' => ['auth']], function () {
    
    // Carpool Dashboard Routes
    Route::prefix('dashboard')->group(function () {
        Route::get('/carpool', [App\Http\Controllers\CarpoolDashboardController::class, 'index'])
            ->name('dashboard.carpool');
        
        Route::get('/carpool/filter', [App\Http\Controllers\CarpoolDashboardController::class, 'getFilteredRoutes'])
            ->name('dashboard.carpool.filter');
        
        Route::get('/carpool/route/{id}/details', [App\Http\Controllers\CarpoolDashboardController::class, 'getRouteDetails'])
            ->name('dashboard.carpool.details');
        
        Route::get('/carpool/export', [App\Http\Controllers\CarpoolDashboardController::class, 'exportRoutes'])
            ->name('dashboard.carpool.export');
    });
    
   });

Route::get('/dashboard/carpool', [App\Http\Controllers\CarpoolDashboardController::class, 'index'])
    ->name('dashboard.carpool.index');



Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    
    Route::prefix('send-notification')->name('send-notification.')->group(function () {
        Route::get('/', [SendNotificationController::class, 'create'])->name('create');
        Route::post('/send', [SendNotificationController::class, 'store'])->name('store');
        Route::post('/get-users', [SendNotificationController::class, 'getUsersByType'])->name('get-users');
    });
    
});


Route::get('/sender', function () {
    return event(new App\Events\NewMessage("hello"));
});

Route::controller(LandingPageController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/contact-us', 'contactUs')->name('contact-us');
    Route::get('/about-us', 'aboutUs')->name('about-us');
    Route::get('/privacy', 'privacy')->name('privacy');
    Route::get('/terms', 'terms')->name('terms');
    Route::get('/test-connection', function () {
        $trip = TripRequest::first();
        try {
            checkPusherConnection(\App\Events\CustomerTripRequestEvent::broadcast($trip->driver, $trip));
        } catch (Exception $exception) {

        }

    });
});
Route::get('track-parcel/{id}', [ParcelTrackingController::class, 'trackingParcel'])->name('track-parcel');

Route::get('add-payment-request', [PaymentRecordController::class, 'index']);

Route::get('payment-success', [PaymentRecordController::class, 'success'])->name('payment-success');
Route::get('payment-fail', [PaymentRecordController::class, 'fail'])->name('payment-fail');
Route::get('payment-cancel', [PaymentRecordController::class, 'cancel'])->name('payment-cancel');
Route::get('/update-data-test', [DemoController::class, 'demo'])->name('demo');
Route::get('sms-test', [DemoController::class, 'smsGatewayTest'])->name('sms-test');

Route::get('trigger', function () {
    broadcast(new \App\Events\SampleEvent('Hello'));
    return true;
});

Route::get('test', function () {
    return view('test');
});
