<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::domain("admin.$domain")->middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])->group(function(){
    Route::get('/', \App\Http\Controllers\Admin\AdminController::class);

    Route::resource('users', \App\Http\Controllers\Admin\UsersController::class, ['as' => 'admin']);
});
