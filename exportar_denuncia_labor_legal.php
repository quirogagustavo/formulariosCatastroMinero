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
    $where[] = "ll_id = $1";
    $params[] = $busqueda_id;
} elseif ($busqueda_expte !== '') {
    $where[] = "expte_siged ILIKE $1";
    $params[] = '%' . $busqueda_expte . '%';
}

$sql = 'SELECT * 
        FROM registro_grafico.gra_cm_labores_legales_pga07';

if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}

$sql .= ' ORDER BY ll_id';

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
echo "<tr><th style='text-align: center;'>LABOR LEGAL</th></tr>";
echo "</table><br>";

echo "<table style='width: 100%; text-align: left;' border='1'>";
echo "<tr><th>NUMERO DE EXPEDIENTE:</th><td>".$row['expte_siged']."</td></tr>";

// Buscar titulares en la tabla 'solicitantes' según el expediente actual
$expediente = $row['expte_siged'];
$titular_sql = "SELECT solicitante, cuit FROM registro_grafico.tbl_solicitantes WHERE expediente = $1 and formulario = 'DENUNCIA DE LABOR LEGAL'";
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
echo "<tr><th>MANIFESTACIÓN DE DESCUBRIMIENTO:</th><td>".$row['denom']."</td></tr>";
echo "<tr><th>DEPARTAMENTO:</th><td>".$row['depto']."</td></tr>";
echo "<tr><th>MINERAL(ES):</th><td>";
            $mineral_sql = "SELECT tm.detalle
                                FROM registro_grafico.tbl_formulario_minerales fm
                                JOIN tipo_minerales tm 
                                ON fm.id_mineral = tm.id_mineral
                                WHERE fm.expediente = $1 
                                AND fm.formulario = 'DENUNCIA DE LABOR LEGAL'";

                $mineral_result = pg_query_params($conn, $mineral_sql, [$expediente]);

            if ($mineral_result && pg_num_rows($mineral_result) > 0) {
            $detalles = [];
            while ($mineral = pg_fetch_assoc($mineral_result)) {
                    $detalles[] = htmlspecialchars(strtoupper($mineral['detalle']));
                    }
                echo implode(", ", $detalles);
                pg_free_result($mineral_result);
            } else {
                echo "No disponible";
            }
echo "</td></tr>";          
echo "<tr><th>TIPO YACIMIENTO:</th><td>".strtoupper($row['tipo_yac'])."</td></tr>";
echo "<tr><th>TIPO LABOR EJECUTADA:</th><td>".strtoupper($row['tipo_labor'])."</td></tr>";

echo "</table><br>";

        echo "<table style='width: 100%; text-align: left;' border='1'>";
        echo '<tr><td colspan="3" style="text-align: center;"><h3>PLANILLA DE COORDENADAS GAUSS KRÜGER GAUSS KRÜGER FAJA 2 POSGAR 2007</h3></td></tr>';
        echo "<tr><th>VERTICES</th><th>ESTE (X)</th><th>NORTE (Y)</th></tr>";
        echo '<td>LABOR LEGAL</td>';
            $lem_sql = "SELECT * FROM registro_grafico.gra_cm_labores_legales_pga07 WHERE expte_siged = $1 AND ll_id = (SELECT MAX(ll_id) FROM registro_grafico.gra_cm_labores_legales_pga07 WHERE expte_siged = $1);";
            $lem_result = pg_query_params($conn, $lem_sql, [$expediente]);

                if ($lem_result && pg_num_rows($lem_result) > 0) {
                while ($lem = pg_fetch_assoc($lem_result)) {
                echo '<td>'.htmlspecialchars(str_replace('.', ',', $lem['e_pga07'])).'</td>';
                echo '<td>'.htmlspecialchars(str_replace('.', ',', $lem['n_pga07'])).'</td>';
                                                            }
                pg_free_result($lem_result);
                } else {
                echo '<td>'."-".'</td>';
                echo '<td>'."-".'</td>';
                }
        echo '</td></tr>';
             
        echo'<tr><td colspan="3" style="text-align: center;"><h6>NOTA: </h6></td></tr>';
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