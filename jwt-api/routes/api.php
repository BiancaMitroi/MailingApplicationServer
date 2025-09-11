<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\UserCheckController;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\SendMailsController;
use App\Http\Controllers\Api\EditProfileController;
use App\Http\Controllers\Api\DeleteAccountController;

Route::get('/test', function() { return 'API is working'; });
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login', [LoginController::class, 'login']);
Route::get('/logout', [LoginController::class, 'logout']);
Route::get('/check-user', [UserCheckController::class, 'checkUser']);
Route::post('/check-users', [UserCheckController::class, 'checkMultiple']);
Route::post('/send', [SendMailsController::class, 'send']);
Route::get('/profile', [EditProfileController::class, 'show']);
Route::put('/edit-profile', [EditProfileController::class, 'update']);
Route::delete('/delete-account', [DeleteAccountController::class, 'destroy']);