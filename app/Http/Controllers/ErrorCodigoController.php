<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ErrorCodigoService;

class ErrorCodigoController extends Controller
{
    protected $service;

    public function __construct(ErrorCodigoService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return view('dashboard.erroresdash');
    }

    public function listar()
    {
        $errores = $this->service->listar();
        return response()->json($errores);
    }

    public function store(Request $request)
    {
        $request->validate([
            'numero_error' => 'required|string|max:50',
            'descripcion' => 'nullable|string',
            'imagen' => 'nullable|image|max:2048' // max 2MB
        ]);

        $datos = $request->only(['numero_error', 'descripcion']);
        $datos['imagen'] = $request->file('imagen');

        $errorCreado = $this->service->crear($datos);

        if ($errorCreado) {
            return response()->json(['mensaje' => 'Error creado con éxito', 'error' => $errorCreado], 201);
        }

        return response()->json(['mensaje' => 'No se pudo crear el error'], 500);
    }
}
