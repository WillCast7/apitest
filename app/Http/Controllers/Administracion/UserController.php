<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\BasadoController;
use App\Models\ConfigParamModel;
use App\Models\RolesModel;
use App\Models\UsuariosModel;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UserController extends BasadoController{
    
    public function index(Request $request) {
        $itemsPerPage = $request->itemsPerPage;
        $activePage = $request->activePage;
        $searched = $request->searched;
        $valueSearch = $request->valueSearch;
        
        try {
            if($itemsPerPage && $activePage){
                $offset = ($activePage - 1) * $itemsPerPage;
                if($searched != 'false' && $valueSearch != 'null'){
                    $usuarios = UsuariosModel::getPaginatedUsuarios(
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
                        $usuarios = UsuariosModel::getPaginatedUsuarios(
                            $itemsPerPage,
                            $offset
                        );
                        $totalFields= UsuariosModel::getTotalUsuarios()[0];
                }
                
                $totalPages = $totalFields->totalusuarios/$itemsPerPage;
                
                if ($totalPages != floor($totalPages)) {
                    $totalPages = intval($totalPages)+1;
                } else {
                    $totalPages = intval($totalPages);
                }

                $data = $usuarios;
                $params[0] = $totalPages;
                $params[1] =  $totalFields->totalusuarios;
                return $this->returnData($data, $params);
            }
        } catch (Exception $error){
            return $this->returnError('Error consultando los datos', 400, $error);
        }
    }


    public function store(Request $request)
{
    $user = BasadoController::getUser();
    $objData = json_decode($request->getContent());
    $personaForm = $objData->personaForm ?? null;
    $usuarioForm = $objData->usuarioForm ?? null;

    try {
        
        $this->checkUniqueConstraints($usuarioForm, $personaForm);

        $this->validateForms($personaForm, $usuarioForm);

        $personaForm->persona_creadopor = $user->userId;
        $usuarioForm->usuario_creadopor = $user->userId;
        $usuarioForm->usuario_contrasena = 'VFZSSmVrNUVWVEpPZW1jOQ=='; //crea una contraseña por defecto y cuando la

        $idPersona = UsuariosModel::setPerson($personaForm)[0]->persona_id;
        UsuariosModel::setUser($usuarioForm, $idPersona);

        return $this->returnOk('El usuario fue registrado correctamente', 200);
    } catch (ValidationException $validationError) {
        return $this->returnError('Error en la validación', 400, $validationError->errors());
    } catch (Exception $error) {
        return $this->returnError('Error al crear el usuario', 500, $error->getMessage());
    }
}

private function checkUniqueConstraints($usuarioForm, $personaForm){
    if (UsuariosModel::getSingleUsuarioDiferentUser($usuarioForm->usuario)[0]->resultado) {
        throw new Exception('Ya existe un usuario con ese nombre de usuario');
    }

    if (UsuariosModel::getSingleUsuarioDiferentDNI(
        $personaForm->persona_dni,
        $personaForm->persona_tipodni
    )[0]->resultado) {
        throw new Exception('Ya existe un usuario con ese número de documento');
    }
}

private function validateForms($personaForm, $usuarioForm){
    $message = ['required' => 'El :attribute es requerido'];

    $personValidator = Validator::make(collect($personaForm)->all(), [
        'persona_tipodni' => 'required',
        'persona_dni' => 'required',
        'persona_primernombre' => 'required',
        'persona_primerapellido' => 'required',
        'persona_telefono' => 'required',
    ], $message);

    $userValidator = Validator::make(collect($usuarioForm)->all(), [
        'rol_id' => 'required',
        'usuario' => 'required',
        'usuario_correo' => 'required',
        'estado' => 'required',
    ], $message);

    if ($personValidator->fails()) {
        throw new ValidationException($personValidator);
    }

    if ($userValidator->fails()) {
        throw new ValidationException($userValidator);
    }
}


    public function show($id){
        $user       = BasadoController::getUser();
        $data = [];
        try {
            if($id!=0){
                $data[0] = UsuariosModel::getSingleUser($id)[0];
                $data[1] = UsuariosModel::getSinglePerson($data[0]->persona_id)[0];
            }
            
            $params[0] = RolesModel::getroles($user->userId);
            $params[1] = ConfigParamModel::GetsimpleParams('TIPO_DNI');
            return $this->returnData($data, $params);
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
                'estado' => 'required'
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
        $objData= json_decode( $request -> getContent() );
        $status = $objData->status ? 'true' : 'false';
        try {
            UsuariosModel::setStatus($objData->usuario_id, $status);
            return $this->returnOk('Actualizado Correctamente', 201);
        } catch (Exception $error){
            return $this->returnError('Error Actualizando los datos', 400, $error);
        }

    }

    public function destroy(Request $request, $id){
        $objData    = json_decode( $request -> getContent() );
        $status = $objData->status ? 'true' : 'false';
        try {
            UsuariosModel::setStatus($objData->usuario_id, $status);
            return $this->returnOk('Actualizado Correctamente', 201);
        } catch (Exception $error){
            return $this->returnError('Error Actualizando los datos', 400, $error);
        }
    }
}
