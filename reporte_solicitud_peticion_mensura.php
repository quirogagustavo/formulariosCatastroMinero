<?php
/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/

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

$result = $params ? pg_query_params($conn, $sql, $params) : null;

/*
if (!$result) {
    echo "Error en la búsqueda: " . pg_last_error($conn);
    exit;
}*/

?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Generar Reporte</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 30px;
    }
    h2 {
      color: #003366;
    }
    form {
      margin-bottom: 30px;
    }
    label {
      margin-right: 10px;
      font-weight: bold;
    }
    input[type="text"] {
      padding: 6px;
      margin-right: 10px;
    }
    button {
      padding: 6px 12px;
    }
    table {
      border-collapse: collapse;
      width: 70%;
      margin-bottom: 30px;
    }
    th, td {
      border: 1px solid #ccc;
      padding: 8px;
      text-align: left;
    }
    .points-table {
      margin-left: 40px;
      width: 60%;
    }
  </style>
</head>
<body>
  <h1>Reporte PEDIDO DE MENSURA</h1>
  <h2>Expediente</h2>

  <form method="get">
    
    <label for="expte">Expediente:</label>
    <input type="text" name="expte" id="expte" value="<?= htmlspecialchars($busqueda_expte) ?>">

    <button type="submit">Buscar</button>

    <?php if ($busqueda_expte): ?>
    <a id="enlace" target="_blank" style="margin-left: 15px; text-decoration:none;">
    
  
  <button type="button">Exportar a Word</button>
  </a>
<?php endif; ?>
  </form>

  <?php if ($result && pg_num_rows($result) > 0): ?>
    <?php if ($row = pg_fetch_assoc($result)): ?>
        <script>
        document.addEventListener("DOMContentLoaded", function() {
        document.getElementById('enlace').href = "exportar_solicitud_peticion_mensura.php?expte=<?= urlencode($row['expte_siged']) ?>";
        });
        
        </script>
        <table>
        <thead>
          <tr>
           <td colspan="2" style="text-align: center;"><h2>PEDIDO DE MENSURA</h2></td>
          </tr>
          <tr>
            <th>Numero de Expediente:</th>
            <td><?= htmlspecialchars($row['expte_siged']) ?></td>
          </tr>
          <tr>
            <th>Solicitante(s):</th>
            <td>
            <?php
            $expediente = $row['expte_siged'];
            $titular_sql = "SELECT solicitante, cuit FROM registro_grafico.tbl_solicitantes WHERE expediente = $1 AND formulario = 'SOLICITUD DE PETICION DE MENSURA'";
            $titular_result = pg_query_params($conn, $titular_sql, [$expediente]);

                if ($titular_result && pg_num_rows($titular_result) > 0) {
                while ($titular = pg_fetch_assoc($titular_result)) {
                echo htmlspecialchars($titular['solicitante']);
                if (!empty($titular['cuit'])) {
                echo " (CUIT: " . htmlspecialchars($titular['cuit']) . ")";
                                              }
                echo "<br>";
                                              }
                pg_free_result($titular_result);
                } else {
                echo "No disponible";
                }
            ?>
            </td>
          </tr>
          <tr>
            <th>Nombre de la Mina:</th>
            <td><?= htmlspecialchars($row['denom']) ?></td>
          </tr>
          <tr>
            <th>Departamento:</th>
            <td><?= htmlspecialchars($row['depto']) ?></td>
          </tr>
          <tr>
          <th>Mineral(es):</th>
            <td>
           <?php
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
                    $detalles[] = htmlspecialchars($mineral['detalle']);
                    }
                echo implode(", ", $detalles);
                pg_free_result($mineral_result);
            } else {
                echo "No disponible";
            }
            ?>
           </td>
          </tr>
          <tr>
            <th>Tipo de Yacimiento:</th>
            <td><?= htmlspecialchars($row['tipo_yac']) ?></td>
          </tr>
        </thead>
        <tbody>     
        </tbody>
      </table>
    
      
<?php endif; ?>
<?php elseif ($result): ?>
    <p>No se encontraron resultados para la búsqueda.<?php exit; ?></p>
<?php elseif ($busqueda_expte): ?>
    <p>Error en la búsqueda: <?= pg_last_error($conn); exit; ?> ?></p>
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
        WHERE t.expte_siged ILIKE $1
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
        WHERE t.expte_siged ILIKE $1
        GROUP BY t.expte_siged, t.geom, t.ll_id
        ORDER BY t.expte_siged, t.ll_id
    ";

  /*  // Pertenencias
    $sql2 = "
         SELECT 
            t.expte_siged,
            t.id_pol,
            t.sup_graf_ha,
            t.sup_solic_ha,
            t.mens_id,
            t.geom AS geom_original,
            json_agg(json_build_object('x', ST_X((dp).geom), 'y', ST_Y((dp).geom))) AS vertices
        FROM registro_grafico.gra_cm_mensura_pertenencias_pga07 t
        JOIN LATERAL ST_DumpPoints(t.geom) AS dp ON true
        WHERE t.expte_siged LIKE $1
        GROUP BY t.expte_siged, t.id_pol, sup_graf_ha, t.sup_solic_ha, t.mens_id, t.geom
        ORDER BY t.expte_siged, t.mens_id
    ";*/

    $sql2 = "
         SELECT 
            t.expte_siged,
            t.id_pol,
            t.sup_reg_ha,
            t.sup_decla_men_ha,
            t.mens_id,
            t.id_pert,
            CASE 
                WHEN ST_IsValid(t.geom) AND ST_NumPoints(t.geom) > 0 THEN
                    (SELECT json_agg(json_build_object('x', ST_X(dp.geom), 'y', ST_Y(dp.geom))) 
                     FROM ST_DumpPoints(t.geom) dp)
                ELSE 
                    '[{\"x\":0,\"y\":0}]'::json
            END AS vertices
        FROM registro_grafico.gra_cm_mensura_pertenencias_pga07 t
        WHERE t.expte_siged ILIKE $1
        ORDER BY t.id_pert::int ASC
    ";

    $result1 = pg_query_params($conn, $sql1, $params);
    $result3 = pg_query_params($conn, $sql3, $params);
    $result2 = pg_query_params($conn, $sql2, $params);

    if ($result1) {
        echo "<h3>COORDENADAS PERIMETRO MINA</h3>";
        while ($row1 = pg_fetch_assoc($result1)) {
            echo "Expte: " . htmlspecialchars($row1['expte_siged']) . 
                 " | ID: " . htmlspecialchars($row1['mensar_id']) . 
                 " | Superficie Declarada: " . $row1['sup_decla_ha'] .
                 " | Superficie Graficada: " . $row1['sup_graf_ha'] . "<br><br>";
            $vertices = json_decode($row1['vertices'], true);
            foreach ($vertices as $v) {
                echo "&nbsp;&nbsp;&nbsp;Vertice: (" . htmlspecialchars(number_format($v['x'], 2, '.', '')) . ", " . htmlspecialchars(number_format($v['y'], 2, '.', '')) . ")<br>";
            }
        echo "<br>";
        }
        
        pg_free_result($result1);
    } else {
        echo "Error en consulta LINEA: " . pg_last_error($conn);
    }

    if ($result3) {
        echo "<h3>COORDENADAS LABOR LEGAL</h3>";
        while ($row3 = pg_fetch_assoc($result3)) {
            echo "Expte: " . htmlspecialchars($row3['expte_siged']) . 
                 " | ID: " . htmlspecialchars($row3['ll_id']) . "<br><br>";
            $vertices3 = json_decode($row3['vertices'], true);
            foreach ($vertices3 as $v) {
                echo "&nbsp;&nbsp;&nbsp;Vertice: (" . htmlspecialchars(number_format($v['x'], 2, '.', '')) . ", " . htmlspecialchars(number_format($v['y'], 2, '.', '')) . ")<br>";
            }
        echo "<br>";
        }
        
        pg_free_result($result3);
    } else {
        echo "Error en consulta LINEA: " . pg_last_error($conn);
    }

    if ($result2) {
        echo "<h3>PLANILLA DE COORDENADAS PERTENENCIAS</h3>";
        while ($row2 = pg_fetch_assoc($result2)) {
            echo "Expte: " . htmlspecialchars($row2['expte_siged']) . 
                 " | Coordenadas Pertenencia:: " . htmlspecialchars($row2['id_pert']) . 
                 " | Polígono: " . htmlspecialchars($row2['id_pol']) . 
                 " | Superficie Solicitada: " . $row2['sup_decla_men_ha'] .
                 " | Superficie Registrada: " . $row2['sup_reg_ha'] . "<br><br>";
            $vertices2 = json_decode($row2['vertices'], true);
            foreach ($vertices2 as $v) {
                echo "&nbsp;&nbsp;&nbsp;Vertice: (" . htmlspecialchars(number_format($v['x'], 2, '.', '')) . ", " . htmlspecialchars(number_format($v['y'], 2, '.', '')) . ")<br>";
            }
            echo "<br>";
        }
        pg_free_result($result2);
    } else {
        echo "Error en consulta POLÍGONO: " . pg_last_error($conn);
    }
}
    ?>

</body>
</html>
<?php

if ($result) pg_free_result($result);
pg_close($conn);
?>



