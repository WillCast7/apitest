<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Administracion\UserController;
use App\Http\Controllers\Configuration\ConfigController;


#Login and register
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::group(['middleware'=>['auth.jwt']], function(){

    #user controller
    Route::controller(UserController::class)->group(function () {
        $endpoint='/administracion/usuarios';
        $id = '/{id}';
        Route::get($endpoint.$id, 'show');
        Route::get($endpoint, 'index');
        Route::post($endpoint, 'store');
        Route::put($endpoint.$id, 'update');
        Route::patch($endpoint.$id, 'edit');
    });

    #Configuracion controller
    Route::controller(ConfigController::class)->group(function () {
        $endpoint='/configuracion';
        $id = '/{id}';
        Route::get($endpoint.$id, 'show');
        Route::get($endpoint, 'index');
        Route::post($endpoint, 'store');
        Route::put($endpoint.$id, 'update');
        Route::patch($endpoint.$id, 'edit');
    });

    #auth controller
    Route::controller(AuthController::class)->group(function() {
        Route::post('/logout', 'logout');
        Route::post('/userProfile', 'userProfile');
    });
    
    #Menu controller
    Route::controller(MenuController::class)->group(function() {
        Route::post('/menu', 'index');
    });

});
