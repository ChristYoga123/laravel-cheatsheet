<?php

use App\Jobs\StoreUserData;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    // Store Data Queue Logic
    $start = microtime(true);
    dispatch(new StoreUserData());
    $end = microtime(true);
    $durasi = $end - $start;
    return "<h1>Halaman diproses dalam ". $durasi . ' detik</h1>';
});
