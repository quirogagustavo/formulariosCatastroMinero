<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

// Conexión a la base de datos
include 'conectar_bd.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!$db) {
    die("Error de conexión a la base de datos.");
}

// Obtener datos del POST
$expediente = $_POST['expediente'] ?? '';
$formulario = $_POST['formulario'] ?? '';
$fecha = $_POST['fecha'] ?? date('Y-m-d');
$estado = $_POST['estado'] ?? '';
$observaciones = trim($_POST['observaciones'] ?? '');

if (!$expediente || !$estado || !$observaciones) {
    die("Error: faltan datos obligatorios.");
}

// Insertar en la tabla
$query = "INSERT INTO registro_grafico.tbl_operacion_expediente (expediente, fecha, estado, observaciones, formulario) VALUES ($1, $2, $3, $4, $5)";
$result = pg_query_params($db, $query, [$expediente, $fecha, $estado, $observaciones, $formulario]);

if ($result) {
    echo "<div style='padding: 2rem; font-family: sans-serif;'><h3>✅ Operación registrada correctamente.</h3><a href='menu.php'>Volver</a></div>";
    echo "<div style='padding: 2rem; font-family: sans-serif;'><h3>📄 Exportar Reporte.</h3><a href='exportar_observacion.php?expediente=".$expediente."'>Exportar Reporte Observación</a></div>";
} else {
    echo "<div style='padding: 2rem; font-family: sans-serif;'><h3 style='color:red;'><a href='observar_expediente.php?expediente=$expediente'>❌ Error al guardar.</h3><p>" . pg_last_error($db) . "</p></div>";
}
