<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RolesModel extends Model{
    use HasFactory;

    public static function getroles($id){
        return DB::select("SELECT rls.rol_id,
                        rls.rol
                    FROM roles rls
                    WHERE rls.estado = TRUE
                        AND rls.rol_nivel >= (SELECT rol_nivel
                    FROM roles rls
                    INNER JOIN usuarios usr
                        ON usr.rol_id = rls.rol_id
                    WHERE usr.usuario_id = {$id})"
        );
    }

    public static function getEntraceById($id){
        return DB::select(
                "SELECT e.entrada_id,
                    i.instructor_nombres,
                    a.alumno_nombres,
                    e.entrada_fcreacion,
                    fe.foto_instructor,
                    fe.foto_alumno
                FROM entradas e
                INNER JOIN instructores i
                    ON i.instructor_id= e.instructor_id
                INNER JOIN alumnos a
                    ON a.alumno_id = e.alumno_id
                INNER JOIN fotosentradas fe
                    ON fe.entrada_id = e.entrada_id
                WHERE e.entrada_id = {$id}"
        );
    }

}
