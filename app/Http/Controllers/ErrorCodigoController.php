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
    try {
        $request->validate([
            'numero_error' => 'required|string|max:50',
            'descripcion' => 'nullable|string',
            'imagen' => 'nullable|image|max:2048'
        ]);

        // Debug para confirmar si llega el archivo
        if (!$request->hasFile('imagen')) {
            return response()->json(['message' => 'No se recibió ninguna imagen'], 400);
        }

        $file = $request->file('imagen');
        if (!$file->isValid()) {
            return response()->json(['message' => 'El archivo de imagen no es válido'], 400);
        }

        $datos = $request->only(['numero_error', 'descripcion']);
        $datos['imagen'] = $file;

        $errorCreado = $this->service->crear($datos);

        return response()->json(['mensaje' => 'Error creado con éxito', 'error' => $errorCreado], 201);
    } catch (\Exception $e) {
        \Log::error('Error en store: ' . $e->getMessage());
        return response()->json(['message' => 'Error al crear el error: ' . $e->getMessage()], 500);
    }
}

}