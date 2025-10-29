<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

?>
<?php
// Obtener expediente por GET
$expediente = $_GET['expediente'] ?? '';
$formulario = $_GET['formulario'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Rechazo de Expediente</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <link href="style.css?v=<?=time()?>" rel="stylesheet" type="text/css" /> 

  <style>
    body {
      background-color: #f8f9fa;
    }
  </style>
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark btn-orange">
    <div class="container-fluid">
      <span class="navbar-text text-white me-auto">
        👤 Usuario: <strong><?php echo htmlspecialchars($_SESSION['usuario']); ?></strong>
      </span>
      <a href="logout.php" class="btn btn-outline-light btn-sm">Cerrar sesión</a>
    </div>
</nav>

  <div class="container py-4 bg-white shadow rounded-3 mt-4" style="max-width: 800px;">
    <h1 class="mb-3">Formulario de Rechazo de Expediente</h1>
    <p class="text-muted mb-4">Complete la observación del rechazo para el expediente ingresado.</p>

    <form method="post" action="guardar_observacion.php">
      <input type="hidden" name="estado" value="rechazado">
      <input type="hidden" name="fecha" value="<?= date('Y-m-d') ?>">

      <div class="mb-3">
        <label class="form-label">Expediente</label>
        <input type="text" name="expediente" class="form-control" readonly value="<?= htmlspecialchars($expediente) ?>" required>
        <input type="hidden" name="formulario" class="form-control" readonly value="<?= htmlspecialchars($formulario) ?>" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Observaciones del rechazo/otras</label>
        <textarea name="observaciones" class="form-control" rows="5" required placeholder="Escriba los motivos del rechazo..."></textarea>
      </div>

      <div class="d-flex justify-content-between">
        <a href="javascript:history.back()" class="btn btn-secondary">Cancelar</a>
        <button type="submit" class="btn btn-danger">Observar Expediente</button>
      </div>
    </form>
  </div>
</body>
</html>
