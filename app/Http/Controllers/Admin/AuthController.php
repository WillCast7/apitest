<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BasadoController;
use App\Models\Auth;
use App\Models\Menu;
use Exception;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends BasadoController{

    public function __construct(){
        $this->middleware('auth:api', ['except' => ['login','register']]);
    }

    //Login function
    public function login(Request $request) {
        $validator = Validator::make ($request->all(), [
            'email' => 'required',
            'password' => 'required'
        ], [
            'required' => 'El :attribute es requerido'
        ]);

        if($validator->fails()){
            return $this->returnError('Error procesando los datos', 400, $validator->errors());
        }

        try{
            $request['password'] = $this->kingdomEncrypt( $request['password'] );

            $response = Auth::login($request);
            if(isset($response[0])){
                $token = array(
                    'iat' => time(),
                    'exp' => time()+(60*60*24),
                    'userId'=> $response[0]->usuario_id
                );

                #Get the menu
                $oldMenu = Menu::getMenu( $response[0]->usuario_id );
                

                
                foreach ($oldMenu as $item) {
                    if (!isset($newMenu[$item->menu_padre])) {
                        $newMenu[$item->menu_padre] = array(
                            'id' => $item->menu_id,
                            'title' => $item->menu_padre,
                            'icon' => $item->menu_icono,
                            'otro' => array()
                        );
                    }
                    array_push($newMenu[$item->menu_padre]['otro'], $item);
                }
                $newMenu = array_values($newMenu);
                $jwtToken = JWT::encode($token, env('KEY_ACCESS'), "HS256");

                return $this->returnData([
                    "token" => $jwtToken,
                    "personName" => $response[0]->nombres,
                    "userName" => $response[0]->usuario,
                    "userId" => $response[0]->usuario_id,
                    "menu" => $newMenu,
                    "action" => "begin session"
                ]);
            }
            return $this->returnError('El usuario no coincide o está desactivado', 401);
        }catch (Exception $error) {
            return $this->returnError('Error consultando los datos', 400, $error);
        }
    }
  
}
