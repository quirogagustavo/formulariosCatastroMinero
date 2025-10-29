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
    SELECT *
    FROM registro_grafico.gra_cm_mensura_area_pga07
";

if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$result = null;

if ($params) {
    // Si hay parámetros, usar pg_query_params
    $result = pg_query_params($conn, $sql, $params);
} else {
    // Si no hay parámetros, ejecutar consulta simple
    $result = pg_query($conn, $sql);
}

if (!$result) {
    echo "Error en la búsqueda: " . pg_last_error($conn);
    exit;
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
        echo "<tr><th style='text-align: center;'>PEDIDO DE MENSURA</th></tr>";
        echo "</table><br>";

        echo "<table style='width: 100%; text-align: left;' border='1'>";
        echo "<tr><th>NUMERO DE EXPEDIENTE:</th><td>".$row['expte_siged']."</td></tr>";
    
            $titulares = [];
            $expediente = $row['expte_siged'];
            $titular_sql = "SELECT solicitante, cuit FROM registro_grafico.tbl_solicitantes WHERE expediente = $1 AND formulario = 'SOLICITUD DE PETICION DE MENSURA'";
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
            echo "<tr><th>NOMBRE DE LA MINA:</th><td>".$row['denom']."</td></tr>";
            echo "<tr><th>DEPARTAMENTO:</th><td>".$row['depto']."</td></tr>";
            echo "<tr><th>MINERALES(ES):</th>";
            echo "<td>";
            $mineral_sql = "SELECT tm.detalle
                                FROM registro_grafico.tbl_formulario_minerales fm
                                JOIN tipo_minerales tm 
                                ON fm.id_mineral = tm.id_mineral
                                WHERE fm.expediente = $1 
                                AND fm.formulario = 'SOLICITUD DE PETICION DE MENSURA'";

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
              echo "<tr><th>TIPO DE YACIMIENTO:</th><td>".strtoupper($row['tipo_yac'])."</td></tr>";
            ?>
            
           </td>
          </tr>
        <?
        
        
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
    // Extraer número base del expediente (sin sufijos como -EXP, -MANIF)
    $expediente_base = preg_replace('/-(EXP|MANIF|SOLICITUD).*$/i', '', $busqueda_expte);
    $params = array($expediente_base . '%');

   // Perimetros
    $sql1 = "
        SELECT 
            t.mensar_id,
            t.expte_siged,
            t.sup_graf_ha,
            t.sup_decla_ha,
            t.geom AS geom_original,
            json_agg(json_build_object('x', ST_X((dp).geom), 'y', ST_Y((dp).geom))) AS vertices
        FROM registro_grafico.gra_cm_mensura_area_pga07 t
        JOIN LATERAL ST_DumpPoints(t.geom) AS dp ON true
        WHERE t.expte_siged LIKE $1
        GROUP BY t.expte_siged, t.sup_graf_ha, t.sup_decla_ha, t.geom, t.mensar_id
        ORDER BY t.expte_siged, t.mensar_id
    ";

    $sql3 = "
        SELECT 
            t.ll_id,
            t.expte_siged,
            t.geom AS geom_original,
            json_agg(json_build_object('x', ST_X((dp).geom), 'y', ST_Y((dp).geom))) AS vertices
        FROM registro_grafico.gra_cm_labores_legales_pga07 t
        JOIN LATERAL ST_DumpPoints(t.geom) AS dp ON true
        WHERE t.expte_siged LIKE $1
        GROUP BY t.expte_siged, t.geom, t.ll_id
        ORDER BY t.expte_siged, t.ll_id
    ";

    // Pertenencias - Consulta simplificada
    $sql2 = "
        SELECT 
            t.expte_siged,
            t.id_pol,
            t.sup_reg_ha,
            t.sup_decla_men_ha,
            t.mens_id,
            t.id_pert,
            t.geom
        FROM registro_grafico.gra_cm_mensura_pertenencias_pga07 t
        WHERE t.expte_siged LIKE $1
        ORDER BY t.id_pert::int ASC
    ";

    $result1 = pg_query_params($conn, $sql1, $params);
    $result3 = pg_query_params($conn, $sql3, $params);
    $result2 = pg_query_params($conn, $sql2, $params);

    if ($result3) {
        
    while ($row3 = pg_fetch_assoc($result3)) {
        echo "<br><table style='width: 100%; border-collapse: collapse;' border='1'>";
        echo "<tr><th colspan='2' style='text-align: left;'>COORDENADAS LABOR LEGAL</th></tr>";
        /*echo "<tr><td style='width: 200px;'>ID:</td><td>" . htmlspecialchars($row3['ll_id']) . "</td></tr>";             */
        echo "</table>";
        // Vértices
        $i=0;
        $vertices = json_decode($row3['vertices'], true);
        echo "<table style='width: 100%; border-collapse: collapse;' border='1'>";
        echo "<tr><th>VERTICES</th><th>ESTE (X)</th><th>NORTE (Y)</th></tr>";
        foreach ($vertices as $v) {
            echo "<tr>";
            echo "<td>L.L. </td>";
            echo "<td>" . htmlspecialchars(number_format($v['x'], 2, ',', '')) . "</td>";
            echo "<td>" . htmlspecialchars(number_format($v['y'], 2, ',', '')) . "</td>";
            echo "</tr>";
            $i++;
        }
        echo "</td></tr>";

               
    echo "</table>";
    }
    
    
        pg_free_result($result3);
    } else {
        echo "Error en consulta Labor Legal: " . pg_last_error($conn);
    }


    if ($result1) {
        
    while ($row1 = pg_fetch_assoc($result1)) {
        echo "<br><table style='width: 100%; border-collapse: collapse;' border='1'>";
        echo "<tr><th colspan='2' style='text-align: left;'>COORDENADAS PERIMETRO MINA</th></tr>";
        /*echo "<tr><td style='width: 200px;'>Poligono ID:</td><td>" . htmlspecialchars($row1['mensar_id']) . "</td></tr>";   */          
        if (!empty($row1['sup_decla_ha'])) {echo "<tr><td>Superficie Declarada:</td><td>" . htmlspecialchars(number_format($row1['sup_decla_ha'], 2, '.', '')) . " ha</td></tr>";}
        echo "<tr><td>Superficie Registrada:</td><td>" . number_format($row1['sup_graf_ha'], 2, '.', '') . " ha </td></tr>";
        echo "</table>";
        // Vértices
        $i=0;
        $vertices = json_decode($row1['vertices'], true);
        echo "<table style='width: 100%; border-collapse: collapse;' border='1'>";
        echo "<tr><th>VERTICES</th><th>ESTE (X)</th><th>NORTE (Y)</th></tr>";
        // comparo último con primero

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
        echo "Error en consulta Perimetro: " . pg_last_error($conn);
    }

    
    if ($result2) {
        echo "<br><table style='width: 100%; border-collapse: collapse;' border='1'>";
        echo "<tr><th colspan='2' style='text-align: left;'>PLANILLA DE COORDENADAS PERTENENCIAS</th></tr>";
        echo "</table>";
    
        while ($row2 = pg_fetch_assoc($result2)) {
        echo "<br><table style='width: 100%; border-collapse: collapse;' border='1'>";
        echo "<tr><td style='width: 200px;'>Coordenadas Pertenencia: </td><td>" . htmlspecialchars($row2['id_pert']) . "</td></tr>";
        
        if (!empty($row2['id_pol'])) {echo "<tr><td>Polígono: </td><td>" . htmlspecialchars($row2['id_pol']) . "</td></tr>";}
                
        if (!empty($row2['sup_decla_men_ha'])) {echo "<tr><td>Superficie Declarada:</td><td>" . htmlspecialchars(number_format($row2['sup_decla_men_ha'], 2, '.', '')) . " ha</td></tr>";}
        
        echo "<tr><td>Superficie Registrada:</td><td>" . number_format($row2['sup_reg_ha'], 2, '.', '') . " ha </td></tr>";
        
        echo "</table>";
        
        // Extraer vértices directamente en PHP
        $vertices = [];
        if ($row2['geom']) {
            // Usar una consulta separada para obtener los vértices
            $sql_vertices = "SELECT ST_X((dp).geom) as x, ST_Y((dp).geom) as y 
                            FROM ST_DumpPoints($1) AS dp";
            $result_vertices = pg_query_params($conn, $sql_vertices, [$row2['geom']]);
            
            if ($result_vertices) {
                while ($vertex = pg_fetch_assoc($result_vertices)) {
                    $vertices[] = ['x' => $vertex['x'], 'y' => $vertex['y']];
                }
                pg_free_result($result_vertices);
            }
        }

        if (empty($vertices)) {
            echo "<table style='width: 100%; border-collapse: collapse;' border='1'>";
            echo "<tr><th colspan='3' style='color: red;'>ERROR: No se pudieron procesar los vértices para esta pertenencia</th></tr>";
            echo "<tr><td>Pertenencia</td><td colspan='2'>" . htmlspecialchars($row2['id_pert']) . " (geometría no válida en base de datos)</td></tr>";
            echo "</table>";
        } else {
            echo "<table style='width: 100%; border-collapse: collapse;' border='1'>";
            echo "<tr><th>VERTICES</th><th>ESTE (X)</th><th>NORTE (Y)</th></tr>";

            $ultimo = count($vertices) - 1;

            // comparo último con primero - solo si hay al menos 2 vértices
            if ($ultimo > 0 && isset($vertices[$ultimo]) && isset($vertices[0]) && 
                $vertices[$ultimo]['x'] == $vertices[0]['x'] && $vertices[$ultimo]['y'] == $vertices[0]['y']) {
                array_pop($vertices); // saco el último
            }

            foreach ($vertices as $i => $v) {
                echo "<tr>";
                echo "<td>V". ($i + 1) . "</td>";
                echo "<td>" . htmlspecialchars(number_format($v['x'], 2, ',', '')) . "</td>";
                echo "<td>" . htmlspecialchars(number_format($v['y'], 2, ',', '')) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
              
    echo "</table>";
    }
    
        pg_free_result($result2);
    } else {
        echo "Error en consulta POLÍGONO: " . pg_last_error($conn);
    }
}

echo "<b>Observaciones:</b> .................... </b><i></i>";
echo "<br><br><h3>REGISTRO CATASTRAL MINERO, ".strtoupper(date('d M Y'))."</h3>";
echo "</body></html>";

?>


</body>
</html>
<?php
if ($result) pg_free_result($result);
pg_close($conn);
?>
