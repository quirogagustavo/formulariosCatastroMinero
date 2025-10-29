<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Panel de Formularios Mineros</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="style.css?v=<?=time()?>" rel="stylesheet" type="text/css" /> 
</head>

<body class="bg-light">
  <nav class="navbar navbar-expand-lg navbar-dark btn-orange">
    <div class="container-fluid">
      <span class="navbar-text text-white me-auto">
        👤 Usuario: <strong><?php echo htmlspecialchars($_SESSION['usuario']); ?></strong>
      </span>
      <a href="logout.php" class="btn btn-outline-light btn-sm">Cerrar sesión</a>
    </div>
  </nav>

  <div class="container py-5">
    <h1 class="text-center mb-5 text-dark">🗂️ Gestión de Formularios y Reportes</h1>
    
    <div class="row g-4">
      
      <div class="col-md-6">
        <div class="card p-4">
          <h4 class="titulo mb-3">NUEVA SOLICITUD DE PERMISO DE EXPLORACIÓN</h4>
          <div class="d-flex gap-3">
            <a href="formulario_solicitud_permiso_exploracion.php" class="btn btn-orange btn-lg w-50">📝 Formulario</a>
            <a href="reporte_solicitud_permiso_exploracion.php" class="btn btn-warning btn-lg w-50">📄 Reporte</a>
          </div>
        </div>
      </div>
      
      <div class="col-md-6">
        <div class="card p-4">
          <h5 class="titulo mb-3">NUEVA SOLICITUD DE MANIFESTACIÓN DE DESCUBRIMIENTO</h5>
          <div class="d-flex gap-3">
            <a href="formulario_solicitud_manifestacion.php" class="btn btn-orange btn-lg w-50">📝 Formulario</a>
            <a href="reporte_solicitud_manifestacion.php" class="btn btn-warning btn-lg w-50">📄 Reporte</a>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card p-4">
          <h4 class="titulo mb-3">NUEVA DENUNCIA DE LABOR LEGAL</h4>
          <div class="d-flex gap-3">
            <a href="formulario_denuncia_labor_legal.php" class="btn btn-orange btn-lg w-50">📝 Formulario</a>
            <a href="reporte_denuncia_labor_legal.php" class="btn btn-warning btn-lg w-50">📄 Reporte</a>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card p-4">
          <h4 class="titulo mb-3">NUEVA SOLICITUD DE PETICIÓN DE MENSURA</h4>
          <div class="d-flex gap-3">
            <a href="formulario_solicitud_peticion_mensura.php" class="btn btn-orange btn-lg w-50">📝 Formulario</a>
            <a href="reporte_solicitud_peticion_mensura.php" class="btn btn-warning btn-lg w-50">📄 Reporte</a>
            
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card p-4">
          <h4 class="titulo mb-3">NUEVA SOLICITUD DE CANTERAS</h4>
          <div class="d-flex gap-3">
            <a href="formulario_solicitud_canteras.php" class="btn btn-orange btn-lg w-50">📝 Formulario</a>
            <a href="reporte_solicitud_canteras.php" class="btn btn-warning btn-lg w-50">📄 Reporte</a>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card p-4">
          <h4 class="titulo mb-3">NUEVA DENUNCIA DE SERVIDUMBRE</h4>
          <div class="d-flex gap-3">
            <a href="formulario_denuncia_servidumbre.php" class="btn btn-orange btn-lg w-50">📝 Formulario</a>
            <a href="reporte_denuncia_servidumbre.php" class="btn btn-warning btn-lg w-50">📄 Reporte</a>
           
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
