<?php

namespace App\Http\Controllers;

use App\Services\UsuarioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UsuarioController extends Controller
{
    protected $usuarioService;

    public function __construct(UsuarioService $usuarioService)
    {
        $this->usuarioService = $usuarioService;
    }

    public function vista()
    {
        return view('dashboard.usersdash');
    }

    
    public function index()
    {
        return response()->json($this->usuarioService->obtenerUsuarios());
    }


    public function store(Request $request)
    {
        $this->usuarioService->crearUsuario(
            $request->nombre,
            $request->email,
            $request->password
        );

        return response()->json(['mensaje' => 'Usuario creado correctamente']);
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'nombre' => 'required|string|max:100',
                'email' => 'required|email|max:100',
            ]);

            $this->usuarioService->actualizarUsuario(
                $id,
                $request->nombre,
                $request->email
            );

            return response()->json(['mensaje' => 'Usuario actualizado correctamente']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function destroy($id)
    {
        $this->usuarioService->eliminarUsuario($id);
        return response()->json(['mensaje' => 'Usuario eliminado correctamente']);
    }
}
