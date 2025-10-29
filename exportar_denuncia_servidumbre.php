<?php

/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/

header("Content-Type: application/vnd.ms-word");
header("Pragma: no-cache");
header("Expires: 0");

include 'conectar_bd.php';

if (!$conn) {
    die("Error de conexión a la base de datos.");
}

// Inicializar búsqueda
$busqueda_expte = $_GET['expte'] ?? '';

// Armar consulta con filtros si se usan
$where = [];
$params = [];

if ($busqueda_expte !== '') {
    $where[] = "expte_siged ILIKE $1";
    $params[] = '%' . $busqueda_expte . '%';
}

$sql = "
    SELECT expte_siged, fecha_solicitud, derecho
    FROM registro_grafico.gra_cm_servidumbres_lin_pga07
";

$sql2 = "
    SELECT expte_siged, fecha_solicitud, derecho
    FROM registro_grafico.gra_cm_servidumbres_pol_pga07
";

// Si hay filtros, se aplican en ambas
if ($where) {
    $sql  .= " WHERE " . implode(' AND ', $where);
    $sql2 .= " WHERE " . implode(' AND ', $where);
}

// Unir resultados de ambas tablas (mismos campos)
$sql_final = "
    ($sql)
    UNION ALL
    ($sql2)
    ORDER BY expte_siged
";

$result = null;

if ($busqueda_expte !== '') {
    // solo ejecuta si se definió el expediente
    $result = $params ? pg_query_params($conn, $sql_final, $params) : pg_query($conn, $sql_final);
}

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
?>
  
  <?php if ($result && pg_num_rows($result) > 0): 
        if ($row = pg_fetch_assoc($result)): 
     
        header("Content-Disposition: attachment;Filename= Informe_".$row['expte_siged'].".doc");        
        echo "<table style='width: 100%;'>";
        echo "<tr><th style='text-align: right;'>Expediente Nº:".$row['expte_siged'].". Folio: .....</th></tr>";
        echo "</table><br>";

        echo "<table style='width: 100%;' border='1'>";
        echo "<tr><th style='text-align: center;'>SERVIDUMBRE</th></tr>";
        echo "</table><br>";

        echo "<table style='width: 100%; text-align: left;' border='1'>";
        echo "<tr><th>NUMERO DE EXPEDIENTE:</th><td>".$row['expte_siged']."</td></tr>";
    
            $titulares = [];
            $expediente = $row['expte_siged'];
            $titular_sql = "SELECT solicitante, cuit FROM registro_grafico.tbl_solicitantes WHERE expediente = $1 AND formulario = 'DENUNCIA DE SERVIDUMBRE'";
            $titular_result = pg_query_params($conn, $titular_sql, [$expediente]);

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
            echo "<tr><th>DERECHOS A LOS QUE SIRVE:</th><td>".$row['derecho']."</td></tr>";
        ?>

        </thead>
        <tbody>     
        </tbody>
      </table>

           
<?php endif; ?>
<?php elseif ($result): ?>
    <p>No se encontraron resultados para la búsqueda.</p>
<?php elseif ($busqueda_expte): ?>
    <p>Error en la búsqueda: <?= pg_last_error($conn) ?></p>
<?php endif; ?>

<?php
if ($busqueda_expte !== '') {
    $params = array('%' . $busqueda_expte . '%');

    // LINEAS agrupando vértices
    $sql1 = "
        SELECT 
            t.expte_siged,
            t.serv_lin_id,
            t.objeto_servidumbre,
            t.tipo_servidumbre,
            t.ancho_servidumbre,
            t.sup_graf_ha,
            t.depto,
            t.geom AS geom_original,
            json_agg(json_build_object('x', ST_X((dp).geom), 'y', ST_Y((dp).geom))) AS vertices
        FROM registro_grafico.gra_cm_servidumbres_lin_pga07 t
        JOIN LATERAL ST_DumpPoints(t.geom) AS dp ON true
        WHERE t.expte_siged ILIKE $1
        GROUP BY t.expte_siged, t.serv_lin_id, t.objeto_servidumbre, t.tipo_servidumbre, t.ancho_servidumbre, t.sup_reg_ha, t.geom
        ORDER BY t.expte_siged, t.serv_lin_id
    ";

    // POLIGONOS agrupando vértices
    $sql2 = "
        SELECT 
            t.expte_siged,
            t.serv_pol_id,
            t.objeto_servidumbre,
            t.tipo_servidumbre,
            t.sup_graf_ha,
            t.depto,
            t.geom AS geom_original,
            json_agg(json_build_object('x', ST_X((dp).geom), 'y', ST_Y((dp).geom))) AS vertices
        FROM registro_grafico.gra_cm_servidumbres_pol_pga07 t
        JOIN LATERAL ST_DumpPoints(t.geom) AS dp ON true
        WHERE t.expte_siged ILIKE $1
        GROUP BY t.expte_siged, t.serv_pol_id, t.objeto_servidumbre, t.tipo_servidumbre, t.sup_reg_ha, t.geom
        ORDER BY t.expte_siged, t.serv_pol_id
    ";

    $result1 = pg_query_params($conn, $sql1, $params);
    $result2 = pg_query_params($conn, $sql2, $params);

    if ($result1) {
        
    while ($row1 = pg_fetch_assoc($result1)) {
        echo "<br><table style='width: 100%; border-collapse: collapse;' border='1'>";
        echo "<tr><th colspan='2' style='text-align: left;'>Servidumbre Tipo Línea</th></tr>";
        echo "<tr><td style='width: 200px;'>Entidad ID:</td><td>" . htmlspecialchars($row1['serv_lin_id']) . "</td></tr>";
        echo "<tr><td>Departamento:</td><td>" . htmlspecialchars($row1['depto']) . "</td></tr>";
        echo "<tr><td>Tipo Servidumbre:</td><td>" . htmlspecialchars($row1['tipo_servidumbre']) . "</td></tr>";
        echo "<tr><td>Objeto Servidumbre:</td><td>" . htmlspecialchars($row1['objeto_servidumbre']) . "</td></tr>";
        echo "<tr><td>Ancho Declarado Camino:</td><td>" . $row1['ancho_servidumbre'] . " m </td></tr>";
        echo "<tr><td>Superficie Registrada Total:</td><td>" . number_format($row1['sup_graf_ha'], 4, ',', '')." ha </td></tr>";
        echo "</table>";
        // Vértices
        $i=0;
        $vertices = json_decode($row1['vertices'], true);

        echo "<table style='width: 100%; border-collapse: collapse;' border='1'>";
        echo "<tr><th>VERTICES</th><th>ESTE (X)</th><th>NORTE (Y)</th></tr>";
        
        $ultimo = count($vertices) - 1;
        if ($vertices[$ultimo]['x'] == $vertices[0]['x'] && $vertices[$ultimo]['y'] == $vertices[0]['y']) {
            array_pop($vertices); // saco el último
        }

        foreach ($vertices as $i => $v) {
            echo "<tr>";
            echo "<td>V". htmlspecialchars($i + 1) . "</td>";
            echo "<td>" . htmlspecialchars(number_format($v['x'], 2, ',', '')) . "</td>";
            echo "<td>" . htmlspecialchars(number_format($v['y'], 2, ',', '')) . "</td>";
            echo "</tr>";
            $i++;
        }
        echo "</td></tr>";

               
    echo "</table>";
    }
    
    
        pg_free_result($result1);
    } else {
        echo "Error en consulta LINEA: " . pg_last_error($conn);
    }

    if ($result2) {
     while ($row2 = pg_fetch_assoc($result2)) {
        echo "<br><table style='width: 100%; border-collapse: collapse;' border='1'>";
        echo "<tr><th colspan='2' style='text-align: left;'>Servidumbre Tipo Polígono</th></tr>";
        echo "<tr><td style='width: 200px;'>Entidad ID:</td><td>" . htmlspecialchars($row2['serv_pol_id']) . "</td></tr>";
        echo "<tr><td>Departamento:</td><td>" . htmlspecialchars($row2['depto']) . "</td></tr>";
        echo "<tr><td>Tipo Servidumbre:</td><td>" . htmlspecialchars($row2['tipo_servidumbre']) . "</td></tr>";
        echo "<tr><td>Objeto Servidumbre:</td><td>" . htmlspecialchars($row2['objeto_servidumbre']) . "</td></tr>";
        echo "<tr><td>Superficie Registrada Total:</td><td>".number_format($row2['sup_graf_ha'], 4, ',', '')." ha </td></tr>";
        echo "</table>";
        $i=0;
        $vertices = json_decode($row2['vertices'], true);
        echo "<table style='width: 100%; border-collapse: collapse;' border='1'>";
        echo "<tr><th>VERTICES</th><th>ESTE (X)</th><th>NORTE (Y)</th></tr>";
        
        $ultimo = count($vertices) - 1;
        if ($vertices[$ultimo]['x'] == $vertices[0]['x'] && $vertices[$ultimo]['y'] == $vertices[0]['y']) {
            array_pop($vertices); // saco el último
        }

        foreach ($vertices as $i => $v) {
            echo "<tr>";
            echo "<td>V". htmlspecialchars($i + 1) . "</td>";
            echo "<td>" . htmlspecialchars(number_format($v['x'], 2, ',', '')) . "</td>";
            echo "<td>" . htmlspecialchars(number_format($v['y'], 2, ',', '')) . "</td>";
            echo "</tr>";
            $i++;
        }
        echo "</td></tr>";
              
    echo "</table>";
    }
    
        pg_free_result($result2);
    } else {
        echo "Error en consulta POLÍGONO: " . pg_last_error($conn);
    }
}

echo "<b>Observaciones:</b> .................... </b><i>Queda registrado el pedido en las coordenadas que anteceden.</i>";
echo "<br><br><h3>REGISTRO CATASTRAL MINERO, ".strtoupper(date('d M Y'))."</h3>";
echo "</body></html>";

?>


</body>
</html>
<?php
if ($result) pg_free_result($result);
pg_close($conn);
?>
