<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class ErrorCodigoService
{
    public function listar()
    {
        return DB::table('errores_codigo')->orderBy('created_at', 'desc')->get();
    }

    public function crear(array $datos)
    {
        $imagenUrl = null;

        if (isset($datos['imagen']) && $datos['imagen']->isValid()) {
            $imagenUrl = Cloudinary::upload($datos['imagen']->getRealPath())->getSecurePath();
        }

        $numero_error = $datos['numero_error'];
        $descripcion = $datos['descripcion'] ?? null;

        // Llamar al SP
        $result = DB::select('CALL sp_insertar_error_codigo(?, ?, ?)', [
            $numero_error,
            $descripcion,
            $imagenUrl
        ]);

        // El SP devuelve un array con un objeto con propiedad 'id'
        $id = $result[0]->id ?? null;

        // Si quieres, puedes devolver el registro recién insertado consultando por id
        if ($id) {
            return DB::table('errores_codigo')->where('id', $id)->first();
        }

        return null;
    }
}
