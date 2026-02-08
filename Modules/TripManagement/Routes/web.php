<?php

use Illuminate\Support\Facades\Route;
use Modules\TripManagement\Http\Controllers\Web\New\RefundController;
use Modules\TripManagement\Http\Controllers\Web\TripController;

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

use Modules\TripManagement\Http\Controllers\Web\CarpoolRouteWebController;

Route::prefix('dashboard/carpool')->middleware(['web','auth'])
    ->name('carpool.')->group(function () {
        Route::get('create', [CarpoolRouteWebController::class, 'create'])->name('create');
        Route::post('store',  [CarpoolRouteWebController::class, 'store'])->name('store');
});

Route::get('dashboard/carpool/vehicles-by-driver/{driver_id}', [CarpoolRouteWebController::class, 'vehiclesByDriver']);


Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => 'admin'], function () {
    Route::group(['prefix' => 'trip', 'as' => 'trip.'], function () {
        Route::controller(\Modules\TripManagement\Http\Controllers\Web\New\TripController::class)->group(function () {
            Route::get('list/{type}', 'tripList')->name('index');
            Route::get('details/{id}', 'show')->name('show');
            Route::get('export', 'export')->name('export');
        });
        Route::controller(TripController::class)->group(function () {
//            Route::get('details/{id}', 'show')->name('show');
            Route::get('invoice/{id}', 'invoice')->name('invoice');
            Route::delete('delete/{id}', 'destroy')->name('delete');
            Route::get('log', 'log')->name('log');
            Route::get('trashed', 'trashed')->name('trashed');
            Route::get('restore/{id}', 'restore')->name('restore');
        });

        Route::group(['prefix' => 'refund', 'as' => 'refund.'], function () {
            Route::controller(RefundController::class)->group(function () {
                Route::get('list/{type}', 'parcelRefundList')->name('index');
                Route::get('details/{id}', 'show')->name('show');
                Route::post('approved/{id}', 'storeApproved')->name('approved');
                Route::post('denied/{id}', 'storeDenied')->name('denied');
                Route::post('store/{id}', 'store')->name('store');
                Route::get('export', 'export')->name('export');
            });
        });
    });

});
