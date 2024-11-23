<!DOCTYPE html>
    <html lang="es">
        <head>
            <title>titulo</title>
        </head>
        <body>
            <page style="font-size: 14px">
                <div style="margin-left:50px; margin-right: 40px;">
                    <div style="text-align: center;">
                        <p>
                            <span>{{ $fcreacion }}</span>
                            <span style="margin-left: 50%;">Factura No. {{ $idFactura }}</span>
                        </p>
                    </div>
                    <hr style="margin-bottom: 40px;">
                    <h1 style="margin-bottom: 50px;">Factura de pago</h1>
                    <h3 style="margin-top: 60px;">
                        Datos del asesor
                    </h3>
                    <div>
                        <div style="display: inline-block;">
                            <span style="font-weight: bold;">
                                {{ $asesor }}
                            </span>
                            <br>
                            <span style="font-weight: bold;">
                                {{ $dni }}
                            </span>  
                        </div>

                        <div style="display: inline-block;">
                            <span style="font-weight: bold;">
                                {{ $telefono }}
                            </span>
                            <br>
                            <span style="font-weight: bold;">
                                {{ $asesor }}
                            </span>  
                        </div>
                    </div>
                    <p style="text-align: center; margin-top: 50px;">
                        Documento equivalente a factura de pago, para la constancia del pago por los servicios realizados al establecimiento desde fechas
                    </p>
                    <hr style="margin-bottom: 70px;">
                    <div style="display: flex; justify-content: center; margin-top: 50px;">
                        <div style="width: 550px; text-align: center; margin: 0 20px; border: solid 2px rgb(53, 53, 53);">
                            <table width="100%">
                                <thead style="font-weight: bold; background-color: #e0e0e0eb;">
                                    <tr>
                                        <td style="width: 30%">
                                            Fecha
                                        </td>
                                        <td style="width: 30%">
                                            Placa
                                        </td>
                                        <td style="width: 30%">
                                            Tipo vehiculo
                                        </td>
                                        <td style="width: 30%">
                                            Comision
                                        </td>
                                    </tr>
                                </thead>
                                <tbody>
                                @php
                                    $total = 0;
                                @endphp
                                    @foreach($tramites as $tramite)
                                        <tr>
                                            <td>
                                                {{ $tramite->fcreacion }}
                                            </td>
                                            <td>
                                                {{ $tramite->tramite_placavehiculo }}
                                            </td>
                                            <td>
                                                {{ $tramite->tramite_tipotramite }}
                                            </td>
                                            <td>
                                                {{ $tramite->costo_formateado }}
                                            </td>
                                        </tr>
                                        @php
                                            $total += $tramite->tramite_costocomision;
                                        @endphp
                                    @endforeach
                                </tbody>
                                <tfoot style="font-weight: bold; background-color: #e0e0e0eb;">
                                    <tr>
                                        <td>
                                            
                                        </td>
                                        <td>
                                            
                                        </td>
                                        <td>
                                            Total
                                        </td>
                                        <td>
                                            {{ '$ ' . number_format($total, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>                    
                    <hr style="margin-top: 40px; margin-bottom: 70px;">

                    <div style="display: flex; justify-content: space-evenly; margin-top: 50px; padding: 0 20px;">
                        <div style="display: flex; flex-direction: column; margin-bottom: 20px; align-items: center;">
                            <span>____________________</span>
                            <br>
                            <span>{{ $asesor }}</span>
                            <br>
                            <span>Asesor</span>
                        </div>
                        <br>
                        <br>
                    
                        <div style="display: flex; flex-direction: column; align-items: center;">
                            <span>____________________</span>
                            <br>
                            <span>{{ $usuario }}</span>
                            <br>
                            <span>Responsable</span>
                        </div>
                    </div>                    
                </div>
            </page>
        </body>
    </html> 