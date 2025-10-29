<?php

/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/

header("Content-Type: application/vnd.ms-word");
header("Pragma: no-cache");
header("Expires: 0");

include 'conectar_bd.php';

$busqueda_id = $_GET['id'] ?? '';
$busqueda_expte = $_GET['expte'] ?? '';

$where = [];
$params = [];
if ($busqueda_id !== '') {
    $where[] = "pexp_id = $1";
    $params[] = $busqueda_id;
} elseif ($busqueda_expte !== '') {
    $where[] = "expte_siged ILIKE $1";
    $params[] = '%' . $busqueda_expte . '%';
}

$sql = 'SELECT * 
        FROM registro_grafico.gra_cm_permisos_exploracion_pga07';

if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY pexp_id';

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
        border: 1px solid black;
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

header("Content-Disposition: attachment;Filename= Informe_".$row['expte_siged'].".doc");        
echo "<table style='width: 100%;'>";
echo "<tr><th style='text-align: right;'>Expediente Nº:".$row['expte_siged'].". Folio: .....</th></tr>";
echo "</table><br>";

echo "<table style='width: 100%;' border='1'>";
echo "<tr><th style='text-align: center;'>PERMISO DE EXPLORACION</th></tr>";
echo "</table><br>";

echo "<table style='width: 100%; text-align: left;' border='1'>";
echo "<tr><th>NUMERO DE EXPEDIENTE:</th><td>".$row['expte_siged']."</td></tr>";

$expediente = $row['expte_siged'];
$titular_sql = "SELECT solicitante, cuit FROM registro_grafico.tbl_solicitantes WHERE expediente = $1";
$titular_result = pg_query_params($conn, $titular_sql, [$expediente]);

$titulares = [];

if ($titular_result && pg_num_rows($titular_result) > 0) {
    while ($titular = pg_fetch_assoc($titular_result)) {
        $linea = htmlspecialchars($titular['solicitante']);
        if (!empty($titular['cuit'])) {
            /*$linea .= " (CUIT: " . htmlspecialchars($titular['cuit']) . ")";*/
            $linea .= ' ';
        }
        $titulares[] = $linea;
    }
    pg_free_result($titular_result);
} else {
    $titulares[] = "No disponible";
}

echo "<tr><th>TITULAR:</th><td>" . implode("<br>", $titulares) . "</td></tr>";
echo "<tr><th>DEPARTAMENTO:</th><td>".$row['depto']."</td></tr>";
echo "<tr><th>PROGRAMA MINIMO DE TRABAJOS:</th><td>".$row['prog_trab']."</td></tr>";
echo "<tr><th>EPOCA DE EXPLORACION:</th><td>".$row['epoca_trab']."</td></tr>";
echo "<tr><th>SUPERFICIE DECLARADA:</th><td>".number_format($row['sup_decl_ha'], 4, '.', '')." ha</td></tr>";
echo "<tr><th>SUPERFICIE REGISTRADA:</th><td>".number_format($row['sup_reg_ha'], 4, '.', '')." ha</td></tr>";
echo "<tr><th>UNIDADES DE MEDIDAS EQUIVALENTES:</th><td>".$row['um_equiv']."</td></tr>";

echo "</table><br>";

        $pid = (int)$row['pexp_id'];
        $points_sql = "
            SELECT (dp).path[1] as punto_num,
                   ST_X((dp).geom) AS x,
                   ST_Y((dp).geom) AS y
            FROM (
              SELECT ST_DumpPoints(geom) AS dp
              FROM registro_grafico.gra_cm_permisos_exploracion_pga07
              WHERE pexp_id = $1
            ) AS foo
            ORDER BY punto_num
        ";

        $points_result = pg_query_params($conn, $points_sql, [$pid]);
        $points = [];
        
        echo "<table style='width: 100%; text-align: left;' border='1'>";
        echo '<tr><td colspan="3" style="text-align: center;"><h3>PLANILLA DE COORDENADAS GAUSS KRÜGER GAUSS KRÜGER FAJA 2 POSGAR 2007</h3></td></tr>';
        echo "<tr><th>VERTICES</th><th>ESTE (X)</th><th>NORTE (Y)</th></tr>";
        
        if ($points_result) {
            while ($p = pg_fetch_assoc($points_result)) {
            $points[] = $p;  
        }
        $total = count($points);
        for ($i = 0; $i < $total - 1; $i++) {  
        
        echo "<tr>";
        echo "<td>V". htmlspecialchars($i + 1) . "</td>";
        echo "<td>" . htmlspecialchars(number_format($points[$i]['x'], 2, ',', '')) . "</td>";
        echo "<td>" . htmlspecialchars(number_format($points[$i]['y'], 2, ',', '')) . "</td>";
        echo "</tr>";
    }

  pg_free_result($points_result);
} else {
            echo "<tr><td colspan='4'>Error obteniendo puntos</td></tr>";
        }
        echo'<tr><td colspan="3" style="text-align: center;"><h6>NOTA: por convención el Vértice V1 corresponde al esquinero superior-izquierdo, los siguientes en sentido horario.</h6></td></tr>';
        echo "</table><br><br>";
    }
} else {
    echo "<p>No se encontraron resultados.</p>";
}
echo "<b>Observaciones:</b> .................... </b><i>Queda registrado el pedido en las coordenadas que anteceden.</i>";
echo "<br><br><h3>REGISTRO CATASTRAL MINERO, ".strtoupper(date('d M Y'))."</h3>";
echo "</body></html>";

if ($result) pg_free_result($result);
pg_close($conn);
?>