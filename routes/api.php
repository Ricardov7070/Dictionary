<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\userManagementController;


Route::post('/auth/signin', [UserManagementController::class, 'userAuthentication']);
Route::post('/auth/signup', [UserManagementController::class, 'registerUsers']);

Route::middleware('auth:sanctum')->group(function () {
    
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

});



