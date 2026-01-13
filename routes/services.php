<?php

use Illuminate\Support\Facades\Route;

// Services
Route::prefix('/ui/services')->group(function(){

    // Провести платежи (ограничение 3 запроса в минуту)
    Route::get('/made-payments', '\App\Http\Controllers\UI\Transactions\Calculator@madePayments')->middleware('throttle:60,1');
});
