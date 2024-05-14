<?php

//use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Main\MainController;
use App\Http\Controllers\Main\AuthController;

Route::get('/', [MainController::class, 'main']);

Route::prefix('auth')->group(function () {
    Route::post('', [AuthController::class, 'store']);
    Route::post('token', [AuthController::class, 'authenticate']);
    Route::get('', [AuthController::class, 'show'])->middleware('auth.jwt');
    Route::put('', [AuthController::class, 'update'])->middleware('auth.jwt');
    Route::put('password/{auth_id}', [AuthController::class, 'update_password'])->middleware('auth.jwt');
    Route::post('password/{auth_id}', [AuthController::class, 'reset_password'])->middleware('auth.jwt');
});


if (class_exists('App\Packages\Example\routes\Example')) {
    // Importe as rotas do pacote Example
    include base_path('app/Packages/Example/routes/Example.php');
}