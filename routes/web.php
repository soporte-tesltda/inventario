<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SendMailController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function (){
    return to_route('filament.admin.auth.login');
})->name('login');

// Ruta para favicon
Route::get('/favicon.ico', function () {
    $faviconPath = public_path('images/logo.ico');
    if (file_exists($faviconPath)) {
        return response()->file($faviconPath, [
            'Content-Type' => 'image/x-icon',
            'Cache-Control' => 'public, max-age=86400', // Cache por 24 horas
        ]);
    }
    return response('', 404);
});

// Ruta alternativa para el favicon desde la carpeta images
Route::get('/images/favicon.ico', function () {
    return redirect('/favicon.ico');
});

