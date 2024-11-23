<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UsuariosModel extends Model{
    use HasFactory;

    public static function getAllUsers($enterprise){
        return DB::select("SELECT CONCAT(p.person_firstname, ' ', p.person_firstlastname) nombre,
                CONCAT(
                    p.person_firstname,
                    ' ',
                    p.person_secondname,
                    ' ',
                    p.person_firstlastname,
                    ' ',
                    p.person_secondlastname
                ) nombreCompleto,
                p.person_dnitype tipoDni,
                p.person_dninumber numeroDni,
                p.person_email correo,
                p.person_phone telefono,
                p.person_gender sexo,
                p.person_phone2 telefonoSecundario,
                p.person_birthdate fechaNacimiento,
                p.person_createdat creado,
                u.user_id,
                u.user_name nombreUsuario,
                u.user_status as status
            FROM users.persons p
            INNER JOIN users.users u
                ON u.person_id = p.person_id
            INNER JOIN enterprises.entusers entu
                ON entu.user_id = u.user_id
            INNER JOIN enterprises.enterprises en
                ON en.enterprise_id = entu.enterprise_id
            WHERE en.enterprise_id={$enterprise}"
        );
    }

    public static function getPaginatedUsuarios($itemsPerPage, $offset, $searched = null, $valueSearch = null){
        $sql = "SELECT
                    usr.usuario_id,
                    concat(per.persona_primernombre, ' ', per.persona_primerapellido) AS nombres,
                    per.persona_dni,
                    usr.usuario,
                    usr.usuario_correo,
                    usr.estado,
                    rl.rol
                FROM usuarios usr
                INNER JOIN personas per 
                    ON per.persona_id = usr.persona_id
                INNER JOIN roles rl
                    ON rl.rol_id = usr.rol_id";

        if ($searched && $valueSearch) {
            $sql .= " WHERE  per.persona_primernombre ILIKE '%{$valueSearch}%' OR
                per.persona_primerapellido ILIKE '%{$valueSearch}%' OR
                per.persona_dni ILIKE '%{$valueSearch}%' OR
                usr.usuario_correo ILIKE '%{$valueSearch}%' OR
                rl.rol ILIKE '%{$valueSearch}%'";
            if(is_numeric($valueSearch)){
                $sql .= " OR usuario_id = '{$valueSearch}'";
            }
        }

    $sql .= " ORDER BY nombres
            LIMIT {$itemsPerPage}
            OFFSET {$offset}";

    return DB::select($sql);

    }

    public static function setStatus($id, $status){
        return DB::update(
            "UPDATE usuarios set estado = {$status} WHERE usuario_id = {$id}"
        );
    }

    public static function getTotalUsuarios($searched = null, $valueSearch = null ){
        $sql=  "SELECT COUNT(*) as totalUsuarios
            FROM usuarios usr
            INNER JOIN personas per 
                ON per.persona_id = usr.persona_id
            INNER JOIN roles rl
                ON rl.rol_id = usr.rol_id ";

        if ($searched && $valueSearch) {
            $sql .= " WHERE  per.persona_primernombre ILIKE '%{$valueSearch}%' OR
                per.persona_primerapellido ILIKE '%{$valueSearch}%' OR
                per.persona_dni ILIKE '%{$valueSearch}%' OR
                usr.usuario_correo ILIKE '%{$valueSearch}%' OR
                rl.rol ILIKE '%{$valueSearch}%'";
            if(is_numeric($valueSearch)){
                $sql .= " OR usuario_id = '{$valueSearch}'";
            }
        }

        return DB::select($sql);
    }

    public static function getPrivileges($id, $enterpriseShortName){
        return DB::connection("AntDB")->select(
            "SELECT
            mp.userprivileges_privileges-> 0 ->> 'usuarios' usuarios,
            mp.userprivileges_privileges-> 1 ->> 'datos_personales' datos_personales,
            mp.userprivileges_privileges-> 2 ->> 'cambiar_contrasena' cambiar_contrasena,
            mp.userprivileges_privileges-> 3 ->> 'asesores' asesores,
            mp.userprivileges_privileges-> 4 ->> 'tramites' tramites,
            mp.userprivileges_privileges-> 5 ->> 'pendiente_pago' pendiente_pago,
            mp.userprivileges_privileges-> 6 ->> 'facturas' facturas
        FROM {$enterpriseShortName}.menuprivileges mp
        WHERE mp.user_id = {$id}"
        );
    }
    
    public static function getCreatedByUser($id){
        return DB::select("SELECT CONCAT(p.person_firstname, ' ', p.person_firstlastname) createdby
            FROM users.persons p
            INNER JOIN users.users u
                ON u.person_id = p.person_id
            WHERE u.user_id = {$id}"
        );
    }

    public static function verifyPass($id, $pass){
        return DB::select("SELECT COUNT(*) > 0 AS exist
                FROM usuarios usu
                WHERE usu.usuario_id = {$id}
                AND usu.usuario_contrasena = '{$pass}'"
        );
    }

    public static function updatePass($id, $pass){
        return DB::update("update usuarios
        set usuario_contrasena = '{$pass}'
        WHERE usuario_id = {$id}"
        );
    }

    public static function getSingleUser($id){
        return DB::select("SELECT
                    usr.usuario_id,
                    usr.persona_id,
                    usr.usuario,
                    usr.usuario_correo,
                    usr.estado,
                    usr.rol_id
                FROM usuarios usr
                WHERE usr.usuario_id = {$id}"
        );
    }
    
    public static function getSinglePerson($id){
        return DB::select("SELECT
                per.persona_id,
                per.persona_tipodni,
                per.persona_dni,
                per.persona_primernombre,
                per.persona_segundonombre,
                per.persona_primerapellido,
                per.persona_segundoapellido,
                per.persona_telefono,
                per.persona_telefono2,
                per.persona_fechanacimiento,
                per.persona_direccion
            FROM personas per
            WHERE per.persona_id = {$id}"
        );
    }

    public static function getUserByUsername($userName, $id = null){
       $sql = "SELECT COUNT(*) > 0 AS exist
            FROM users.users u
            WHERE u.user_name = '{$userName}'";
        if ($id != null){
            $sql=$sql." AND u.user_id != {$id}";
        };
       return DB::select( $sql);
    }

    public static function getUserByEmail($email, $id = null){
        $sql = "SELECT COUNT(*) > 0 AS exist
            FROM users.users u
            WHERE u.user_email  = '{$email}'";
        if ($id != null){
            $sql=$sql." AND u.user_id != {$id}";
        };
        return DB::select( $sql);
    }

    public static function getProfileByID($id){
        return DB::select(
            "SELECT profile_id
            FROM users.users
            WHERE user_id = $id"
        );
    }

    public static function setPerson( $form ){
        foreach($form as $key=>$value){
            if($value != ''){
                $sqlInsert[]    = $key;
                $sqlBind[]      = '?';
                $sqlValues[]    = $value;
            }
        }
        
        $sqlInsert = implode(',',$sqlInsert);
        $sqlBind = implode(',',$sqlBind);
        $sql = "INSERT INTO personas ($sqlInsert) values($sqlBind) returning persona_id";
        return DB::select($sql, $sqlValues);
    }

    public static function setUser( $form, $idPersona ){
        $form->{"persona_id"} = $idPersona;
        foreach($form as $key=>$value){
            if($value != ''){
                $sqlInsert[]    = $key;
                $sqlBind[]      = '?';
                $sqlValues[]    = $value;
            }
        }
        
        $sqlInsert = implode(',',$sqlInsert);
        $sqlBind = implode(',',$sqlBind);
        $sql = "INSERT INTO usuarios ($sqlInsert) values($sqlBind)";
        return DB::select($sql, $sqlValues);
    }

    public static function updateUser( $form, $id ){

       // fecha actualizacion
       $form->{"usuario_factualizacion"} = 'now()';

       $sql = "UPDATE usuarios set ";
       $sqlSets = [];
       $sqlValues = [];
       foreach($form as $key => $value){
           $sqlSets[] = " $key = ? ";
           $sqlValues[] = $value;
       }
       $sqlSets = implode(',',$sqlSets);
       $sql .= $sqlSets . " where usuario_id = ? returning persona_id";

       // id actualizacion
       $sqlValues[] = $id;
       $result = DB::select($sql,$sqlValues);
       return $result;
    }

    public static function getSingleUsuarioDiferentUser($usuario, $id = null){
        $sql =  "SELECT COUNT(*) <> 0 AS resultado
                FROM usuarios usr
                WHERE usr.usuario = '{$usuario}' ";
        $sql = $id ? $sql." AND usr.usuario_id != {$id}" : $sql;

        return DB::select($sql);
    }

    public static function getSingleUsuarioDiferentDNI($dni, $tipodni, $id = null){
        $sql =  "SELECT COUNT(*) <> 0 AS resultado
                FROM personas prs
                WHERE prs.persona_dni = '{$dni}'
                AND prs.persona_tipodni = '{$tipodni}'";
        $sql = $id ? $sql." AND prs.persona_id != {$id}" : $sql;
        return DB::select($sql);
    }

    public static function updatePerson($form, $id){
       // fecha actualizacion
       $form->{"persona_factualizacion"} = 'now()';

       $sql = "UPDATE personas set ";
       $sqlSets = [];
       $sqlValues = [];
       foreach($form as $key => $value){
           $sqlSets[] = " $key = ? ";
           $sqlValues[] = $value;
       }
       $sqlSets = implode(',',$sqlSets);
       $sql .= $sqlSets . " where persona_id = ?";

       // id actualizacion
       $sqlValues[] = $id;
       $result = DB::select($sql,$sqlValues);
       return $result;
    }

    public static function updateUserStatus($form, $id){
        $value =($form->status == 1)? 0 : 1;
        return DB::update("UPDATE users.users set user_status = {$value} WHERE user_id = {$id}");
    }

}
