<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BasadoController;

use App\Models\Menu;
use Exception;
use Illuminate\Http\Request;

class MenuController extends BasadoController{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(){
        try {
            $user = $this->getUser();
            $data = Menu::getMenu( $user->userId );
            return $this->returnData( $data );
        } catch (Exception $error){
            return $this->returnError( 'Error consultando los datos', 400, $error );
        }
    }

}
