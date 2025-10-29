<?php
// Redirigir automáticamente al login
header("Location: login.php");
exit;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Catastro Minero - Índice</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet" type="text/css">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h1 class="h3 mb-0">🏛️ Sistema de Catastro Minero</h1>
                        <p class="mb-0">Provincia de San Juan</p>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>📝 Formularios de Solicitud</h5>
                                <div class="list-group mb-4">
                                    <a href="formulario_solicitud_manifestacion.php" class="list-group-item list-group-item-action">
                                        📋 Solicitud de Manifestación de Descubrimiento
                                    </a>
                                    <a href="formulario_solicitud_peticion_mensura.php" class="list-group-item list-group-item-action">
                                        📐 Solicitud de Petición de Mensura
                                    </a>
                                    <a href="formulario_solicitud_permiso_exploracion.php" class="list-group-item list-group-item-action">
                                        🔍 Solicitud de Permiso de Exploración
                                    </a>
                                    <a href="formulario_solicitud_canteras.php" class="list-group-item list-group-item-action">
                                        🏗️ Solicitud de Canteras
                                    </a>
                                </div>
                                
                                <h5>📄 Formularios de Denuncia</h5>
                                <div class="list-group mb-4">
                                    <a href="formulario_denuncia_labor_legal.php" class="list-group-item list-group-item-action">
                                        ⚖️ Denuncia de Labor Legal
                                    </a>
                                    <a href="formulario_denuncia_servidumbre.php" class="list-group-item list-group-item-action">
                                        🛤️ Denuncia de Servidumbre
                                    </a>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <h5>📊 Reportes y Consultas</h5>
                                <div class="list-group mb-4">
                                    <a href="reporte_solicitud_manifestacion.php" class="list-group-item list-group-item-action">
                                        📈 Reporte Manifestaciones
                                    </a>
                                    <a href="reporte_solicitud_peticion_mensura.php" class="list-group-item list-group-item-action">
                                        📈 Reporte Peticiones de Mensura
                                    </a>
                                    <a href="reporte_solicitud_permiso_exploracion.php" class="list-group-item list-group-item-action">
                                        📈 Reporte Permisos de Exploración
                                    </a>
                                    <a href="reporte_solicitud_canteras.php" class="list-group-item list-group-item-action">
                                        📈 Reporte Canteras
                                    </a>
                                </div>

                                <h5>🔧 Herramientas</h5>
                                <div class="list-group mb-4">
                                    <a href="buscar_expediente_get.php" class="list-group-item list-group-item-action" target="_blank">
                                        🔍 Búsqueda de Expedientes (API)
                                    </a>
                                    <a href="validar_punto.php" class="list-group-item list-group-item-action" target="_blank">
                                        📍 Validar Coordenadas (API)
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <h6>📋 Expediente de Prueba</h6>
                            <p class="mb-1">Para probar el sistema, usa este expediente:</p>
                            <code>1124-000205-2022-EXP-MANIF</code>
                            <ul class="mt-2 mb-0">
                                <li><strong>Repartición:</strong> 1124</li>
                                <li><strong>N° Expediente:</strong> 000205</li>
                                <li><strong>Año:</strong> 2022</li>
                            </ul>
                        </div>
                        
                        <div class="text-center mt-4">
                            <a href="login.php" class="btn btn-primary btn-lg">
                                🔐 Iniciar Sesión
                            </a>
                        </div>
                    </div>
                    <div class="card-footer text-muted text-center">
                        Sistema de Catastro Minero - Gobierno de San Juan
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>