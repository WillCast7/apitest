<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\BasadoController;
use App\Models\ConfigParamModel;
use App\Models\RolesModel;
use App\Models\UsuariosModel;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ConfigController extends BasadoController{
    
    public function index(Request $request) {
        $itemsPerPage = $request->itemsPerPage;
        $activePage = $request->activePage;
        $searched = $request->searched;
        $valueSearch = $request->valueSearch;
        try {

            if($itemsPerPage && $activePage){
                $offset = ($activePage - 1) * $itemsPerPage;
                if($searched && $valueSearch){
                    $allMedicine = UsuariosModel::getPaginatedUsuarios(
                        $itemsPerPage,
                        $offset,
                        $searched,
                        $valueSearch
                    );
                    $totalFields= UsuariosModel::getTotalUsuarios(
                        $searched,
                        $valueSearch
                        )[0];
                } else{
                    $allMedicine = UsuariosModel::getPaginatedUsuarios(
                        $itemsPerPage,
                        $offset
                    );
                    $totalFields= UsuariosModel::getTotalUsuarios()[0];
                }
                $totalPages = $totalFields->totalmedicamentos/$itemsPerPage;
                
                if ($totalPages != floor($totalPages)) {
                    $totalPages = intval($totalPages)+1;
                } else {
                    $totalPages = intval($totalPages);
                }

                $data[0] = $allMedicine;
                $data[1] = $totalPages;
                $data[2] =  $totalFields->totalmedicamentos;
                return $this->returnData($data, $totalFields, $totalFields);
            }
        } catch (Exception $error){
            return $this->returnError('Error consultando los datos', 400, $error);
        }
    }

    public function store(Request $request){
        
                $user   = BasadoController::getUser();
                $objData= json_decode( $request -> getContent() );
                $personaform   = $objData->personaForm;
                $usuarioform   = $objData->usuarioForm;
                try {
                    if(UsuariosModel::getSingleUsuarioDiferentUser($usuarioform->usuario)[0]->resultado){
                        return $this->returnError('Ya existe un usuario con ese nombre de usuario', 400);
                    }
                    if(UsuariosModel::getSingleUsuarioDiferentDNI(
                        $personaform->persona_dni,
                        $personaform->persona_tipodni)[0]->resultado){
                        return $this->returnError('Ya existe un usuario con ese numero de documento', 400);
                    }

                    $message = ['required' => 'El :attribute es requerido'];

                    $personValidator = Validator::make(collect($personaform)->all(), [
                        'persona_tipodni' => 'required',
                        'persona_dni' => 'required',
                        'persona_primernombre' => 'required',
                        'persona_primerapellido' => 'required',
                        'persona_telefono' => 'required'
                    ], $message);

                    $userValidator = Validator::make(collect($usuarioform)->all(), [
                        'rol_id' => 'required',
                        'usuario' => 'required',
                        'usuario_correo' => 'required',
                        'usuario_estado' => 'required'
                    ], $message);

                    if($personValidator->fails()){
                        return $this->returnError('Error registrando los datos', 400, $personValidator->errors());
                    }
                    if($userValidator->fails()){
                        return $this->returnError('Error registrando los datos', 400, $userValidator->errors());
                    }
                    $personaform->{"persona_creadopor"} = $user->userId;
                    $usuarioform->{"usuario_creadopor"} = $user->userId;
                    $usuarioform->{"usuario_contrasena"} = 'VFZSSmVrNUVWVEpPZW1jOQ==';
                    $idPersona = UsuariosModel::setPerson($personaform)[0]->persona_id;
                    UsuariosModel::setUser($usuarioform, $idPersona);

                    return $this->returnOk('El usuario fue actualizado correctamente', 200);
                    
                }catch(Exception $error){
                    return $this->returnError('Error al editar el usuario', 400, $error);

        }

    }

    public function show($id){
        $user       = BasadoController::getUser();
        try {
            $data[0] = UsuariosModel::getSingleUser($user->userId)[0];
            $data[1] = UsuariosModel::getSinglePerson($data[0]->persona_id)[0];
            $data[2] = RolesModel::getroles($user->userId);
            $data[3] = ConfigParamModel::GetUsuariosParams()[0];
            return $this->returnData($data);
        } catch (Exception $error){
            return $this->returnError('Error consultando los datos', 400, $error);
        }
    }

    public function edit(Request $request, $id){
        $user   = BasadoController::getUser();
        $objData= json_decode( $request -> getContent() );
        $personaform   = $objData->personaform;
        $usuarioform   = $objData->usuarioform;

        try {
            if(UsuariosModel::getSingleUsuarioDiferentUser($usuarioform->usuario, $id)[0]->resultado){
                return $this->returnError('Ya existe un usuario con ese nombre de usuario', 400);
            }
            if(UsuariosModel::getSingleUsuarioDiferentDNI($personaform->persona_dni, $personaform->persona_tipodni, $id)[0]->resultado){
                return $this->returnError('Ya existe un usuario con ese numero de documento', 400);
            }
            $message = ['required' => 'El :attribute es requerido'];

            $personValidator = Validator::make(collect($personaform)->all(), [
                'persona_tipodni' => 'required',
                'persona_dni' => 'required',
                'persona_primernombre' => 'required',
                'persona_primerapellido' => 'required',
                'persona_telefono' => 'required'
            ], $message);

            $userValidator = Validator::make(collect($usuarioform)->all(), [
                'rol_id' => 'required',
                'usuario' => 'required',
                'usuario_correo' => 'required',
                'usuario_estado' => 'required'
            ], $message);

            if($personValidator->fails()){
                return $this->returnError('Error registrando los datos', 400, $personValidator->errors());
            }
            if($userValidator->fails()){
                return $this->returnError('Error registrando los datos', 400, $userValidator->errors());
            }

            $idPersona = UsuariosModel::updateUser($usuarioform, $id, $user->userId)[0]->persona_id;
            UsuariosModel::updatePerson($personaform, $idPersona);

            return $this->returnOk('El usuario fue actualizado correctamente', 200);
            
        }catch(Exception $error){
            return $this->returnError('Error al editar el usuario', 400, $error);
        }
    }

    public function update(Request $request, $id){
        $user   = BasadoController::getUser();
        $objData= json_decode( $request -> getContent() );

        if($objData->validationPass != $objData->pass){
            return $this->returnError('Las contraseñas no coinciden', 400);
        }
        
        if( !UsuariosModel::verifyPass(
            $user->userId,
            $this->kingdomEncrypt($objData->oldPass)
        )[0]->exist){
            return $this->returnError('La contraseña anterior no coincide con el usuario en sesion', 400);
        }
        
        try {
            UsuariosModel::updatePass(
                $user->userId,
                $this->kingdomEncrypt($objData->pass)
            );
            return $this->returnOk('Contraseña actualizada Correctamente', 201);
        } catch (Exception $error){
            return $this->returnError('Error Actualizando los datos', 400, $error);
        }

    }
}
