<?php

namespace App\Http\Controllers;

use App\Services\UsuarioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    protected $usuarioService;

    public function __construct(UsuarioService $usuarioService)
    {
        $this->usuarioService = $usuarioService;
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $usuario = $this->usuarioService->obtenerUsuarioPorEmail($request->email);

        if (!$usuario || !Hash::check($request->password, $usuario->password)) {
            return back()->with('error', 'Credenciales inválidas');
        }

        $request->session()->put('usuario', [
            'id' => $usuario->id,
            'nombre' => $usuario->nombre,
            'email' => $usuario->email
        ]);

        return redirect()->route('dashboard.dashboard');
    }

}
