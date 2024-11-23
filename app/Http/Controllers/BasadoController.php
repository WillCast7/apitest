<?php

namespace App\Http\Controllers;

use App\Models\Auth;
use App\Models\EnterprisesModel;
use App\Models\User;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class BasadoController extends Controller{
    public function __construct() {
        $errorConsulting = 'Error consultando los datos';
        $errorRegistring = 'Error registrando los datos';
        $errorValidating = 'Compruebe todos los campos obligatorios';
        $registeredSuccessfully = 'Registrado Correctamente';
    }

    public function returnError($msg, $responseCode, $error=null, $action=null) {
        $response = array(
            'success'   => false,
            'message'   => $msg,
            'data'      => null,
            'action'    => $action,
            'error'     => $error
        );
        return response()->json($response, $responseCode);
    }

    public function returnOk($msg, $responseCode) {
        $response = array(
            'success'   => true,
            'message'   => $msg
        );
        return response()->json($response, $responseCode);
    }

    public function returnData($response, $addData=null) {
        $correctConsultData = 'Datos consultados correctamente';

        if (!empty($response) || $addData != null){
            if (!empty($addData)){
                if (sizeof($response) > 0){
                    $response = array(
                        'success'   => true,
                        'message'   => $correctConsultData,
                        'data'      => $response,
                        'params'    => $addData
                    );
                } else {
                    $response = array(
                        'success'   => true,
                        'message'   => $correctConsultData,
                        'data'      => null,
                        'params'    => $addData
                    );
                }
                return json_encode($response);
            } else {
                $response = array(
                    'success'   => true,
                    'message'   => $correctConsultData,
                    'data'      => $response
                );
                return response()->json($response, 200);
            }

        } else {
            $response = array(
                'success'   => true,
                'message'   => 'No se obtuvieron datos',
                'data'      => null
            );
            return response()->json($response, 205);
        }
    }

    public function getUser(){
        $header = apache_request_headers();

        if(isset($header['Authorization']) || isset($header['authorization'])){
            $authorization = isset($header['Authorization']) ? $header['Authorization'] : $header['authorization'];
            try{
                return JWT::decode($authorization, new Key(env('KEY_ACCESS'), 'HS256'));
            }catch(Exception $err){
                return $this->returnError('Acceso denegado', 400, $err);
            }
        }else{
            exit();
        }
    }

    //obtener privilegios
    public function getPrivileges($id, $enterpriseShortName){
        return "User::getPrivileges($id, $enterpriseShortName)";
    }

    // TODO funcion para validar permisos
    public function validatePermissions($user, $enterprise){
        return Auth::enterpriseLicence($user, $enterprise);
    }

    public function kingdomDecrypt($var){
        return base64_decode($var);
    }

    public function kingdomEncrypt($var){
        return base64_encode($var);
    }
}
