<?php

namespace App\Http\Controllers;

use App\Services\ErrorCodigoService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Exception;

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
        return response()->json($this->service->listar());
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'numero_error' => 'required|string|max:50',
                'descripcion' => 'nullable|string',
                'imagen' => 'nullable|image|max:2048'
            ]);

            if (!$request->hasFile('imagen') || !$request->file('imagen')->isValid()) {
                return response()->json(['message' => 'El archivo de imagen no es válido o no fue enviado'], 400);
            }

            $datos = $request->only(['numero_error', 'descripcion']);
            $datos['imagen'] = $request->file('imagen');

            $errorCreado = $this->service->crear($datos);

            return response()->json([
                'mensaje' => 'Error creado con éxito',
                'error' => $errorCreado
            ], 201);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->errors()], 422);
        } catch (Exception $e) {
            \Log::error('Error al crear el error', ['exception' => $e]);
            return response()->json(['message' => 'Error al crear el error: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->service->eliminar($id);
            return response()->json(['message' => 'Eliminado correctamente']);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al eliminar el error: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        return response()->json($this->service->obtener($id));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'numero_error' => 'required|string|max:50',
            'descripcion' => 'nullable|string',
            'imagen_url' => 'nullable|image|max:2048'
        ]);

        $datos = $request->only(['numero_error', 'descripcion']);

        if ($request->hasFile('imagen_url')) {
            $datos['imagen_url'] = $request->file('imagen_url');
        }   

        $this->service->actualizar($id, $datos);

        return response()->json(['success' => true]);
    }
}
