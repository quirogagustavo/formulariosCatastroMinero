<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="style.css?v=<?=time()?>" rel="stylesheet" type="text/css" /> 
</head>

<body class="bg-light d-flex justify-content-center align-items-center vh-100">

  <div class="card shadow p-4" style="width: 350px;">
    <h4 class="text-center mb-4">Iniciar sesión</h4>
    <form method="POST" action="login_validar.php">
      <div class="mb-3">
        <label class="form-label">Usuario</label>
        <input type="text" class="form-control" name="usuario" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Clave</label>
        <input type="password" class="form-control" name="clave" required>
      </div>
      <button type="submit" class="btn btn-orange w-100">Ingresar</button>
    </form>
  </div>

</body>
</html>
