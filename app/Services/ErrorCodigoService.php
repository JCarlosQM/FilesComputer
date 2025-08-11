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

    // 🔍 Debug: Verificar que el índice 'imagen' existe
    if (!isset($datos['imagen'])) {
        \Log::error('No se recibió el campo "imagen" en $datos');
        throw new \Exception('No se recibió el campo imagen');
    }

    // 🔍 Debug: Validar que el archivo fue subido y es válido
    if (!($datos['imagen'] instanceof \Illuminate\Http\UploadedFile)) {
        \Log::error('El campo "imagen" no es un archivo válido', [
            'tipo' => gettype($datos['imagen']),
            'contenido' => $datos['imagen']
        ]);
        throw new \Exception('El campo imagen no es un archivo válido');
    }

    if (!$datos['imagen']->isValid()) {
        \Log::error('El archivo "imagen" no es válido', [
            'error_code' => $datos['imagen']->getError()
        ]);
        throw new \Exception('El archivo imagen no es válido');
    }

    // 🔍 Debug: Info del archivo antes de subir
    \Log::info('Archivo recibido', [
        'nombre' => $datos['imagen']->getClientOriginalName(),
        'mime'   => $datos['imagen']->getMimeType(),
        'size'   => $datos['imagen']->getSize(),
        'real_path' => $datos['imagen']->getRealPath()
    ]);

    dd(
        Cloudinary::upload(
            request()->file('imagen')->getRealPath(),
            ['folder' => 'test']
        )->getSecurePath()
    );
    

    // 📤 Subir imagen a Cloudinary
    try {
        $imagenUrl = Cloudinary::upload(
            $datos['imagen']->getRealPath(),
            ['folder' => 'my_laravel_app']
        )->getSecurePath();

        \Log::info('Imagen subida a Cloudinary: ' . $imagenUrl);
    } catch (\Exception $e) {
        \Log::error('Error al subir a Cloudinary', [
            'mensaje' => $e->getMessage(),
            'trace'   => $e->getTraceAsString()
        ]);
        throw new \Exception('Error al subir la imagen a Cloudinary: ' . $e->getMessage());
    }

    $numero_error = $datos['numero_error'];
    $descripcion = $datos['descripcion'] ?? null;

    // 💾 Llamar al procedimiento almacenado
    try {
        $result = DB::select('CALL sp_insertar_error_codigo(?, ?, ?)', [
            $numero_error,
            $descripcion,
            $imagenUrl
        ]);

        \Log::info('Resultado del procedimiento almacenado: ' . json_encode($result));

        if (is_array($result) && !empty($result) && isset($result[0]->id)) {
            $id = $result[0]->id;
            \Log::info('ID obtenido del procedimiento: ' . $id);
            return DB::table('errores_codigo')->where('id', $id)->first();
        }

        \Log::error('El procedimiento sp_insertar_error_codigo no devolvió un ID válido');
        throw new \Exception('El procedimiento no devolvió un ID válido');
    } catch (\Exception $e) {
        \Log::error('Error ejecutando sp_insertar_error_codigo: ' . $e->getMessage());
        throw $e;
    }
}

}
