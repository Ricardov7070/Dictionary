<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\userManagementController;
use App\Http\Controllers\Api\wordsManagementController;
use App\Http\Controllers\Api\wordsApiProxyController;

Route::get('/', [UserManagementController::class, 'index']);
Route::get('/words/{word}', [wordsApiProxyController::class, 'fetchWordDetails']);

Route::post('/auth/signin', [UserManagementController::class, 'userAuthentication']);
Route::post('/auth/signup', [UserManagementController::class, 'registerUsers']);
Route::post('/auth/forgotPassword', [UserManagementController::class, 'forgotPassword']);

Route::middleware('auth:sanctum')->group(function () {
    
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/viewRecord', [UserManagementController::class, 'viewRecord']);
    Route::put('/updateRecord/{id_user}', [UserManagementController::class, 'updateRecord']);
    Route::post('/logoutUser/{id_user}', [UserManagementController::class, 'logoutUser']);
    Route::delete('/deleteRecord/{id_user}', [UserManagementController::class, 'deleteRecord']);

    Route::get('/entries/en', [wordsManagementController::class, 'wordIndex']);
    Route::get('/entries/en/{word}', [wordsManagementController::class, 'selectSpecificWord']);
    Route::post('/entries/en/{id_user}/{word}/favorite', [wordsManagementController::class, 'favoriteRecord']);
    Route::delete('/entries/en/{id_user}/{word}/unfavorite', [wordsManagementController::class, 'removeFavorite']);
    
    Route::get('/user/me/', [UserManagementController::class, 'viewAuthenticatedProfile']);
    Route::get('/user/me/{id_user}/history', [wordsManagementController::class, 'viewSelectedRecords']);
    Route::post('/user/me/{id_user}/favorites', [wordsManagementController::class, 'viewFavoriteRecords']);

});



