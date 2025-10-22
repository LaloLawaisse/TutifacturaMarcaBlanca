<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Connector\Http\Controllers\Api\SellController;

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

// Ruta para obtener el usuario autenticado (opcional)
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

