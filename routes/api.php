<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix' => 'v1'], function () {
    Route::group(['prefix' => 'bank'], function () {
        Route::get('tochka/connect/{state}', "\\App\\Http\\Controllers\\BankController@tochkaConnect");
        Route::get('tochka', "\\App\\Http\\Controllers\\BankController@tochkaCallback");
        Route::get('account/activate', "\\App\\Http\\Controllers\\BankController@accountActivate")->name('checking-account.activate');
        Route::get('account/save', "\\App\\Http\\Controllers\\BankController@saveActivate")->name('checking-account.save');
        Route::get('account/tochka/customers', "\\App\\Http\\Controllers\\BankController@tochkaCustomers")->name('tochka-account.customers');
        Route::get('account/test', "\\App\\Http\\Controllers\\BankController@testActivate")->name('checking-account.test');
    });
});
