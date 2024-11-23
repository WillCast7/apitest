<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Menu extends Model{
    use HasFactory;
//TODO falta validar si la licencia esta vigente, un where con la fecha
    public static function getMenu($userId){
        return DB::select(
            "SELECT mn.menu_id,
                mn.menu_nombre,
                mn.menu_icono,
                mn.menu_padre,
                mn.menu_ruta
            FROM usuarios u
            INNER JOIN roles r
            ON r.rol_id = u.rol_id
            INNER JOIN menu_roles mp
                ON mp.rol_id = r.rol_id
            INNER JOIN public.menus mn
                ON mn.menu_id = mp.menu_id
            WHERE mp.estado = true
            AND u.usuario_id = {$userId}
            ORDER BY mn.menu_orden;"
        );
    }

    public static function getPrivileges($userId){
        return DB::select(
            "SELECT
                mp.userprivileges_privileges-> 0 ->> 'usuarios' usuarios,
                mp.userprivileges_privileges-> 1 ->> 'datos_personales' datos_personales,
                mp.userprivileges_privileges-> 2 ->> 'cambiar_contrasena' cambiar_contrasena,
                mp.userprivileges_privileges-> 3 ->> 'asesores' asesores,
                mp.userprivileges_privileges-> 4 ->> 'tramites' tramites,
                mp.userprivileges_privileges-> 5 ->> 'pendiente_pago' pendiente_pago,
                mp.userprivileges_privileges-> 6 ->> 'facturas' facturas
            FROM users.users u
            INNER JOIN users.menuprivileges mp
                ON mp.user_id = u.user_id
            WHERE u.user_id ={$userId}"
        );
    }

}
