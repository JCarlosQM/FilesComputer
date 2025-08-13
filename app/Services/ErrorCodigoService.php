<?php

namespace App\Services;

use Cloudinary\Cloudinary as CloudinarySDK;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use Exception;

class ErrorCodigoService
{
    public function listar()
    {
        return DB::table('errores_codigo')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function crear(array $datos)
    {
        if (!isset($datos['imagen']) || !($datos['imagen'] instanceof UploadedFile) || !$datos['imagen']->isValid()) {
            throw new Exception('El archivo imagen no es válido o no fue enviado.');
        }

        // Subir imagen a Cloudinary
        $cloudinary = new CloudinarySDK([
            'cloud' => [
                'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                'api_key' => env('CLOUDINARY_KEY'),
                'api_secret' => env('CLOUDINARY_SECRET')
            ]
        ]);

        try {
            $upload = $cloudinary->uploadApi()->upload(
                $datos['imagen']->getRealPath(),
                ['upload_preset' => 'my_laravel_app']
            );

            $imagenUrl = $upload['secure_url'] ?? null;
        } catch (Exception $e) {
            throw new Exception('Error al subir la imagen a Cloudinary: ' . $e->getMessage());
        }

        $numero_error = $datos['numero_error'];
        $descripcion = $datos['descripcion'] ?? null;

        // Guardar en BD mediante procedimiento almacenado
        try {
            $result = DB::select('CALL sp_insertar_error_codigo(?, ?, ?)', [
                $numero_error,
                $descripcion,
                $imagenUrl
            ]);

            if (!empty($result) && isset($result[0]->id)) {
                return DB::table('errores_codigo')->where('id', $result[0]->id)->first();
            }

            throw new Exception('El procedimiento no devolvió un ID válido.');
        } catch (Exception $e) {
            throw new Exception('Error ejecutando sp_insertar_error_codigo: ' . $e->getMessage());
        }
    }
    

    public function eliminar($id)
    {
        DB::table('errores_codigo')->where('id', $id)->delete();
    }

    public function obtener($id)
    {
        return DB::table('errores_codigo')->where('id', $id)->first();
    }

    public function actualizar($id, array $datos)
    {
        $registro = DB::table('errores_codigo')->where('id', $id)->first();

        if (!$registro) {
            throw new Exception('Registro no encontrado');
        }

        $cloudinary = new \Cloudinary\Cloudinary([
            'cloud' => [
                'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                'api_key' => env('CLOUDINARY_KEY'),
                'api_secret' => env('CLOUDINARY_SECRET')
            ]
        ]);

        if (isset($datos['imagen_url']) && $datos['imagen_url'] instanceof \Illuminate\Http\UploadedFile) {
            // 1️⃣ Eliminar la imagen anterior en Cloudinary (si existe)
            if (!empty($registro->imagen_url)) {
                $completelink = $this->extraerPublicId($registro->imagen_url);
                $publicId = Str::after($completelink, '/');

                if ($publicId) {
                    try {
                        $cloudinary->uploadApi()->destroy($publicId);
                    } catch (\Exception $e) {
                        throw new Exception('Error al eliminar imagen anterior: ' . $e->getMessage());
                    }
                }
            }


            // 2️⃣ Subir nueva imagen
            try {
                $upload = $cloudinary->uploadApi()->upload(
                    $datos['imagen_url']->getRealPath(),
                    ['upload_preset' => 'my_laravel_app']
                );
                $imagenUrl = $upload['secure_url'] ?? $registro->imagen_url;
            } catch (\Exception $e) {
                throw new Exception('Error al subir la imagen a Cloudinary: ' . $e->getMessage());
            }
        } else {
            // Mantener la imagen existente
            $imagenUrl = $registro->imagen_url;
        }

        // 3️⃣ Actualizar registro
        DB::table('errores_codigo')->where('id', $id)->update([
            'numero_error' => $datos['numero_error'],
            'descripcion' => $datos['descripcion'],
            'imagen_url' => $imagenUrl,
            'updated_at' => now()
        ]);
    }

    /**
     * Extraer el public_id desde la URL de Cloudinary
     */
    private function extraerPublicId($url)
    {
        $path = parse_url($url, PHP_URL_PATH);
        $parts = explode('/', $path);
        $folderAndFile = array_slice($parts, -2);
        $filename = pathinfo($folderAndFile[1], PATHINFO_FILENAME);
        return $folderAndFile[0] . '/' . $filename;
    }
}
