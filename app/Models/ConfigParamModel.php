<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ConfigParamModel extends Model{
    use HasFactory;

    public static function GetParams(){
        return DB::select(
            "SELECT
            (
                SELECT json_agg(
                    json_build_object(
                        'id', tmp.param_id,
                        'name', tmp.param_name,
                        'shortname', tmp.param_shortname
                    )
                )::json
                FROM (
                    SELECT cp.param_name, cp.param_shortname, cp.param_id
                    FROM configuration_params cp
                    WHERE cp.param_type = 'document_type'
                    ORDER BY cp.param_order
                ) as tmp
            ) AS documenttypes,
            (
                SELECT json_agg(
                    json_build_object(
                        'id', tmp.param_id,
                        'name', tmp.param_name,
                        'shortname', tmp.param_shortname
                    )
                )::json
                FROM (
                    SELECT cp.param_name, cp.param_shortname, cp.param_id
                    FROM configuration_params cp
                    WHERE cp.param_type = 'gender_type'
                    ORDER BY cp.param_order
                ) as tmp
            ) AS genders;"
        );
    }
    
    public static function GetCoinsAndBills(){
        return DB::select(
            "SELECT
            (
                SELECT json_agg(
                    json_build_object(
                        'denominacion', tmp.parametro_valor,
                        'cantidad', 0,
                        'valor', 0,
                        'nombre', tmp.parametro_nombre
                    )
                )::json
                FROM (
                    SELECT cp.parametro_nombre, cp.parametro_valor, cp.parametro_id
                    FROM parametros_configuracion cp
                    WHERE cp.parametro_tipo = 'DENOMINACION_MONEDA'
                    ORDER BY cp.parametro_orden
                ) as tmp
            ) AS coins,
            (
                SELECT json_agg(
                    json_build_object(
                        'denominacion', tmp.parametro_valor,
                        'cantidad', 0,
                        'valor', 0,
                        'nombre', tmp.parametro_nombre
                    )
                )::json
                FROM (
                    SELECT cp.parametro_nombre, cp.parametro_valor, cp.parametro_id
                    FROM parametros_configuracion cp
                    WHERE cp.parametro_tipo = 'DENOMINACION_BILLETE'
                    ORDER BY cp.parametro_orden
                ) as tmp
            ) AS bills;"
        );
    }
    
    public static function GetNewCoinsAndBills(){
        return [
            DB::select("SELECT cp.parametro_nombre AS nombre,
                    cp.parametro_valor AS denominacion,
                    0 AS cantidad,
                    0 AS valor
                FROM parametros_configuracion cp
                WHERE cp.parametro_tipo = 'DENOMINACION_MONEDA'
                ORDER BY cp.parametro_orden"
                    ),
            DB::select("SELECT cp.parametro_nombre AS nombre,
                    cp.parametro_valor AS denominacion,
                    0 AS cantidad,
                    0 AS valor
                FROM parametros_configuracion cp
                WHERE cp.parametro_tipo = 'DENOMINACION_BILLETE'
                ORDER BY cp.parametro_orden"
                        )
        ];
    }

    public static function GetsimpleParams($param){
        return DB::select("SELECT pc.parametro_id,
                        pc.parametro_nombre,
                        pc.parametro_valor
                    FROM parametros_configuracion pc
                    WHERE pc.parametro_tipo = '{$param}'
                    AND pc.estado = true
                    ORDER BY pc.parametro_orden"
            );
    }
}
