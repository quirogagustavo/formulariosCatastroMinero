<?php

/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/

header("Content-Type: application/vnd.ms-word");
header("Pragma: no-cache");
header("Expires: 0");

include 'conectar_bd.php';

$busqueda_expte = $_GET['expediente'] ?? '';

$where = [];
$params = [];

if ($busqueda_expte !== '') {
    $where[] = "expediente ILIKE $1";
    $params[] = '%' . $busqueda_expte . '%';
}

$sql = "
    SELECT *
    FROM registro_grafico.tbl_operacion_expediente
";

if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$result = null;

$result = $params ? pg_query_params($conn, $sql, $params) : null;

echo "<html lang='es'><body>";
echo "<head>";
echo '<meta charset="UTF-8">';
echo "<style>
    body, b, i, tr, h3 {
        font-family: Arial, sans-serif;
        font-size: 12pt;
    }
    table {
        border-collapse: collapse;
        width: 100%;
    }
    th, td {
        border: 0px;
        padding: 4px;
        text-align: left;
    }
    th {
        font-weight: bold;
    }
</style>";
echo "</head>";


if ($result && pg_num_rows($result) > 0) {
    while ($row = pg_fetch_assoc($result)) {

header("Content-Disposition: attachment;Filename= Informe_".$row['expediente'].".doc");        
echo "<table style='width: 100%;'>";
echo "<tr><th style='text-align: right;'>Expediente Nº:".$row['expediente'].". Folio: .....</th></tr>";
echo "</table><br>";

echo "<table style='width: 100%;' border='1'>";
echo "<tr><th style='text-align: left;'>Sr. Director de Registro Minero y Catastro:</th></tr>";
echo "<tr><th style='text-align: left;'>.............................................................</th></tr>";
echo "</table><br>";

echo "<table style='width: 100%;' border='1'>";
echo "<tr><td style='text-align: left;'>Se cumple en informar que no se registra <b>".$row['formulario']." </b> tramitado en autos, por cuanto <b>". $row['observaciones'] .", </b> incumpliendo de esta forma con lo establecido en el artículo Nro. ...... del Código de Minería.</td></tr>";
echo "</table><br>";

echo "<table style='width: 100%; text-align: left;' border='1'>";
echo "<tr><th>REGISTRO CATASTRAL MINERO, ".strtoupper(date('d M Y'))."</td></tr>";

echo "</body></html>";
    }
}

pg_close($conn);
?>