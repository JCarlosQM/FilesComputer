<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ErrorCodigoController;
use App\Http\Controllers\UsuarioController;

Route::get('/ver-env', function () {
    return response()->json([
        'key' => env('CLOUDINARY_KEY'),
        'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
        'secret' => env('CLOUDINARY_SECRET'),
        'url' => env('CLOUDINARY_URL'),
    ]);
});


/*
|--------------------------------------------------------------------------
| Rutas Públicas
|--------------------------------------------------------------------------
*/

Route::get('/', fn() => view('users.home'))->name('home');

Route::get('/login', fn() => view('auth.login'))->name('login')->middleware('guest');

Route::post('/login', [LoginController::class, 'login'])->name('login.submit');

Route::get('/logout', function () {
    session()->forget('usuario');
    return redirect()->route('login')->with('error', 'Sesión cerrada');
})->name('logout');

/*
|--------------------------------------------------------------------------
| Rutas protegidas con verificación manual de sesión
|--------------------------------------------------------------------------
*/

Route::middleware([])->group(function () {

    Route::get('/dashboard', function () {
        if (!session()->has('usuario')) {
            return redirect()->route('login')->with('error', 'Iniciá sesión primero');
        }
    
        return view('dashboard.dashboard');
    })->name('dashboard');

    Route::get('/errores', function () {
        if (!session()->has('usuario')) {
            return redirect()->route('login')->with('error', 'Iniciá sesión primero');
        }
        $controller = app()->make(ErrorCodigoController::class);
        return $controller->index();
    })->name('errores.index');

    Route::get('/api/errores', function () {
        if (!session()->has('usuario')) {
            abort(401, 'No autorizado');
        }
        $controller = app()->make(ErrorCodigoController::class);
        return $controller->listar();
    })->name('errores.api.listar');

    Route::post('/api/errores', function () {
        if (!session()->has('usuario')) {
            abort(401, 'No autorizado');
        }
        return app()->call([ErrorCodigoController::class, 'store']);
    })->name('errores.api.store');


    Route::post('/usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
    Route::put('/usuarios/{id}', [UsuarioController::class, 'update'])->name('usuarios.update');
    Route::delete('/usuarios/{id}', [UsuarioController::class, 'destroy'])->name('usuarios.destroy');
    // ✅ Ruta que carga la vista Blade con la tabla y modals
    Route::get('/usuarios', [UsuarioController::class, 'vista'])->name('usuarios.index');

    // ✅ Ruta API que devuelve JSON (usada por JS)
    Route::get('/api/usuarios', [UsuarioController::class, 'index'])->name('usuarios.api');

});
