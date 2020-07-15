<?php

use Illuminate\Http\Request;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login', 'AuthController@login');
Route::post('/barang', 'BarangController@store');
Route::get('/barang', 'BarangController@show');
Route::put('/barang', 'BarangController@update');
Route::post('/barang/pinjam', 'BarangController@edit');
Route::get('/barang/riwayat', 'BarangController@history');