<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;

class Auth extends Authenticatable{
    use HasApiTokens, HasFactory, Notifiable;

    public static function login($request){
        return DB::select("SELECT
                usrs.usuario_id,
                usrs.usuario,
                concat(pers.persona_primernombre, ' ', pers.persona_primerapellido) AS nombres
            FROM usuarios usrs
            INNER JOIN personas pers
                ON pers.persona_id =usrs.persona_id
            INNER JOIN roles rls
                ON rls.rol_id = usrs.rol_id 
            WHERE (usrs.usuario = '{$request->email}'
                OR usrs.usuario_correo = '{$request->email}')
            AND usrs.usuario_contrasena = '{$request->password}'
            AND usrs.estado = true"
        );
    }

    public static function insertToken($token, $exp, $id){
        return DB::update("UPDATE users SET api_token = '{$token}', token_exp = '{$exp}' where user_id = {$id}");
    }

    public static function verifyPass($pass, $id){
        return DB::select("SELECT COUNT(*) AS exist
            from users
            WHERE user_id = {$id}
            AND user_password = '{$pass}'");
    }

    public static function updatePass($pass, $id){
        return DB::update("UPDATE usuarios SET usuario_contrasena = '{$pass}' where usuario_id = {$id}");
    }

    public static function verifyLicence($user){
        return DB::connection("antDB")->select(
            "SELECT
                usr.user_name,
                licty.licencetype_duration,
                lic.licence_start,
                NOW() AS now,
                lic.licence_start + licty.licencetype_duration AS expiration_date,
                NOW() <= lic.licence_start + licty.licencetype_duration AS iscurrent,
                lic.licence_start + licty.licencetype_duration-NOW() AS timeleft
            FROM users.users usr
            INNER JOIN enterprises.entusers ent
                ON ent.user_id = usr.user_id
            INNER JOIN enterprises.enterprises enterp
                ON enterp.enterprise_id = ent.enterprise_id
            INNER JOIN licences.licences lic
                ON lic.enterprise_id = enterp.enterprise_id
            INNER JOIN licences.licencetypes licty
                ON licty.licencetype_id = lic.licencetype_id
            WHERE usr.user_status = TRUE
            AND usr.user_id = {$user}"
        );
    }
    
    public static function verifyPrivileges($user, $schema){
        return DB::connection("antDB")->select(
            "SELECT
                mp.userprivileges_privileges-> 0 ->> 'usuarios' usuarios,
                mp.userprivileges_privileges-> 1 ->> 'datos_personales' datos_personales,
                mp.userprivileges_privileges-> 2 ->> 'cambiar_contrasena' cambiar_contrasena,
                mp.userprivileges_privileges-> 3 ->> 'asesores' asesores,
                mp.userprivileges_privileges-> 4 ->> 'tramites' tramites,
                mp.userprivileges_privileges-> 5 ->> 'pendiente_pago' pendiente_pago,
                mp.userprivileges_privileges-> 6 ->> 'facturas' facturas
            FROM {$schema}.menuprivileges mp
            WHERE mp.user_id ={$user}"
        );
    }

    public static function enterpriseLicence($user, $enterpriseId){
        return DB::select(
            "SELECT
            NOW() <= lic.licence_start + licty.licencetype_duration AS iscurrent
          FROM users.users usr
          INNER JOIN enterprises.entusers ent
              ON ent.user_id = usr.user_id
          INNER JOIN enterprises.enterprises enterp
              ON enterp.enterprise_id = ent.enterprise_id
          INNER JOIN licences.licences lic
              ON lic.enterprise_id = enterp.enterprise_id
          INNER JOIN licences.licencetypes licty
              ON licty.licencetype_id = lic.licencetype_id
          WHERE usr.user_status = TRUE
              AND usr.user_id = {$user}"
        );
    }
}
