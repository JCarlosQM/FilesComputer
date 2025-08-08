<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerificaSesion
{
    public function handle(Request $request, Closure $next)
    {
        if (!session()->has('usuario')) {
            return redirect()->route('login')->with('error', 'Iniciá sesión primero');
        }
        return $next($request);
    }
}
