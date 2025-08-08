<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class UsuarioService
{
    public function obtenerUsuarioPorEmail($email)
    {
        $resultado = DB::select("CALL sp_obtener_usuario_por_email(?)", [$email]);
        return count($resultado) > 0 ? $resultado[0] : null;
    }

    public function obtenerUsuarios()
    {
        return DB::select("CALL sp_obtener_usuarios()");
    }

    public function crearUsuario($nombre, $email, $password)
    {
        return DB::statement("CALL sp_insert_usuario(?, ?, ?)", [
            $nombre,
            $email,
            bcrypt($password),
        ]);
    }

    public function actualizarUsuario($id, $nombre, $email)
    {
        return DB::statement("CALL sp_update_usuario(?, ?, ?)", [
            $id,
            $nombre,
            $email
        ]);
    }

    public function eliminarUsuario($id)
    {
        return DB::statement("CALL sp_delete_usuario(?)", [$id]);
    }
}
