<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ErrorCodigoController;
use App\Http\Controllers\UsuarioController;

/*
|--------------------------------------------------------------------------
| Rutas Públicas
|--------------------------------------------------------------------------
*/

Route::get('/', [ErrorCodigoController::class, 'indexUser'])->name('home');

Route::get('/buscar', [ErrorCodigoController::class, 'buscar'])->name('buscar');



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
        // Asegura que el contenedor instancie el controlador (evita llamada estática)
        return app()->call(\App\Http\Controllers\ErrorCodigoController::class . '@store');
    })->name('errores.api.store');

    Route::delete('/errores/{id}', [ErrorCodigoController::class, 'destroy']);

    Route::get('/errores/{id}', [ErrorCodigoController::class, 'show']);
Route::put('/errores/{id}', [ErrorCodigoController::class, 'update']);


    Route::post('/usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
    Route::put('/usuarios/{id}', [UsuarioController::class, 'update'])->name('usuarios.update');
    Route::delete('/usuarios/{id}', [UsuarioController::class, 'destroy'])->name('usuarios.destroy');
    // ✅ Ruta que carga la vista Blade con la tabla y modals
    Route::get('/usuarios', [UsuarioController::class, 'vista'])->name('usuarios.index');



    // ✅ Ruta API que devuelve JSON (usada por JS)
    Route::get('/api/usuarios', [UsuarioController::class, 'index'])->name('usuarios.api');

});
