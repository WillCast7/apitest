<?php

namespace App\Http\Middleware;

use App\Http\Controllers\BasadoController;
use Closure;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTAuth extends BasadoController{

    public function handle($request, Closure $next){
        $header = apache_request_headers();
        try{
            
            if(isset($header['Authorization']) && !empty($header['Authorization'])){
                $jwt = $header['Authorization'];
            }elseif(isset($header['authorization']) && !empty($header['authorization'])){
                $jwt = $header['authorization'];
            }else{
                $response = array(
                    'success'   => true,
                    'message'   => 'Debe iniciar sesion para continuar',
                    'data'      => null,
                    'action'    => 'closeSession'
                );
                return response()->json($response, 401);
            }
            $jwt = str_replace("Bearer ", "", $jwt);
            $key = env('KEY_ACCESS');

            $decode = JWT::decode($jwt, new Key($key, 'HS256'));
            if(is_object($decode)){
                return $next($request);
            }else{
                echo json_encode( array(
                    'success'   => false,
                    'message'   => 'hubo un error en la decodificacion de los datos de sesion',
                    'action'    => 'closeSession'
                ));
                exit();
            }

        }catch(Exception $e){
            $response = array(
                'success'   => true,
                'message'   => $e->getMessage(),
                'data'      => null,
                'action'    => 'closeSession'
            );
            return response()->json($response, 401);
        }
            exit();


    }
}
