<?php
session_start();
include 'conectar_bd.php';

$usuario = $_POST['usuario'] ?? '';
$clave   = $_POST['clave'] ?? '';

$sql = "SELECT * 
        FROM registro_grafico.tbl_usuarios_acceso
        WHERE usuario = $1 
          AND clave = $2 
          ";

$res = pg_query_params($conn, $sql, [$usuario, $clave]);

if ($res && pg_num_rows($res) > 0) {
    $row = pg_fetch_assoc($res);
    $_SESSION['usuario'] = $row['usuario'];
    $_SESSION['id']      = $row['id'];
    header("Location: menu.php");
    exit;
} else {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
      <meta charset="utf-8">
      <title>Login - Error</title>
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
      <link href="style.css" rel="stylesheet" type="text/css" /> 
    </head>
    <body class="bg-light d-flex justify-content-center align-items-center vh-100">

      <div class="card shadow p-4 text-center" style="width: 350px;">
        <h5 class="text-danger mb-3">Error de acceso</h5>
        <p class="mb-3">Usuario o clave incorrectos<br>o usuario no habilitado.</p>
        <a href="login.php" class="btn btn-secondary w-100">Volver</a>
      </div>

    </body>
    </html>
    <?php
}