<?php

/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/

include 'conectar_bd.php';

if (!$conn) {
    die("Error de conexión a la base de datos.");
}

// Inicializar búsqueda
$busqueda_id = $_GET['id'] ?? '';
$busqueda_expte = $_GET['expte'] ?? '';

// Armar consulta con filtros si se usan
$where = [];
$params = [];
if ($busqueda_id !== '') {
    $where[] = "cant_id = $1";
    $params[] = $busqueda_id;
} elseif ($busqueda_expte !== '') {
    $where[] = "expte_siged ILIKE $1";
    $params[] = '%' . $busqueda_expte . '%';
}

$sql = 'SELECT * 
        FROM registro_grafico.gra_cm_canteras_pga07';

if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}

$sql .= ' ORDER BY cant_id';

$result = $params ? pg_query_params($conn, $sql, $params) : null;
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
  <h1>Reporte de Canteras</h1>
  <h2>Ingresar ID o Expediente</h2>

  <form method="get">
    <label for="id">ID:</label>
    <input type="text" name="id" id="id" value="<?= htmlspecialchars($busqueda_id) ?>">

    <label for="expte">Expediente:</label>
    <input type="text" name="expte" id="expte" value="<?= htmlspecialchars($busqueda_expte) ?>">

    <button type="submit">Buscar</button>
<?php if ($busqueda_id || $busqueda_expte): ?>
  <a id="enlace" target="_blank" style="margin-left: 15px; text-decoration:none;">
    
  
  <button type="button">Exportar a Word</button>
  </a>
<?php endif; ?>
  </form>

  <?php if ($result && pg_num_rows($result) > 0): ?>
    <?php while ($row = pg_fetch_assoc($result)): ?>
        <script>
        document.addEventListener("DOMContentLoaded", function() {
        document.getElementById('id').value = <?php echo json_encode($row['cant_id']); ?>;
        document.getElementById('enlace').href = "exportar_solicitud_canteras.php?id=<?= urlencode($row['cant_id']) ?>&expte=<?= urlencode($row['expte_siged']) ?>";
         
        
        });
        
        </script>
        <table>
        <thead>
          <tr>
           <td colspan="2" style="text-align: center;"><h2>CANTERA</h2></td>
          </tr>
          <tr>
            <th>ID:</th>
            <td><?= htmlspecialchars($row['cant_id']) ?></td>
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
            $titular_sql = "SELECT solicitante, cuit FROM registro_grafico.tbl_solicitantes WHERE expediente = $1 AND formulario = 'SOLICITUD DE CANTERAS'";
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
            <th>Nombre de la Cantera:</th>
            <td><?= htmlspecialchars($row['denom']) ?></td>
          </tr>
          <tr>
            <th>Departamento:</th>
            <td><?= htmlspecialchars($row['depto']) ?></td>
          </tr>
          <tr>
            <th>Inmueble N.C. Nº:</th>
            <td><?= htmlspecialchars($row['nc']) ?></td>
          </tr>
          <tr>
            <th>Plano Nº:</th>
            <td><?= htmlspecialchars($row['plano_mens']) ?></td>
          </tr>
          <tr>
            <th>Proyecto de Aprovechamiento:</th>
            <td><?= htmlspecialchars($row['aprovechamiento']) ?></td>
          </tr>
          <tr>
            <th>Situación de la Zona:</th>
            <td><?= htmlspecialchars($row['sit_zona']) ?></td>
          </tr>
          <tr>
            <th>Plazo Solicitado (años):</th>
            <td><?= htmlspecialchars($row['tiempo_concesion']) ?></td>
          </tr>
          <tr>
            <th>Actividad:</th>
            <td>CANTERA</td>
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
                                AND fm.formulario = 'SOLICITUD DE CANTERAS'";

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
            <th>Superficie Solicitada:</th>
            <td><?= htmlspecialchars(number_format($row['sup_solic_ha'], 2, '.', '')) ?> ha</td>
          </tr>
          <tr>
            <th>Superficie Registrada:</th>
            <td><?= htmlspecialchars(number_format($row['sup_reg_ha'], 2, '.', '')) ?> ha</td>
          </tr>
          
        </thead>
        <tbody>
          
          
        </tbody>
      </table>

      <table class="points-table">
        <thead>
          <tr>
            <td colspan="3" style="text-align: center;"><h3>PLANILLA DE COORDENADAS GAUSS KRÜGER GAUSS KRÜGER FAJA 2 POSGAR 2007</h3></td>
          </tr>
          <tr>
            <th>VERTICES</th>
            <th>ESTE (X)</th>
            <th>NORTE (Y)</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $i=1;
          $pid = (int)$row['cant_id'];
          $points_sql = "
            SELECT (dp).path[1] as punto_num,
                   ST_X((dp).geom) AS x,
                   ST_Y((dp).geom) AS y
            FROM (
              SELECT ST_DumpPoints(geom) AS dp
              FROM registro_grafico.gra_cm_canteras_pga07
              WHERE cant_id = $1
            ) AS foo
            ORDER BY punto_num
          ";
          $points_result = pg_query_params($conn, $points_sql, [$pid]);
$points = [];

if ($points_result) {
  while ($p = pg_fetch_assoc($points_result)) {
    $points[] = $p;  
  }

  $total = count($points);

  for ($i = 0; $i < $total - 1; $i++) {  // recorremos excluyendo el último
    echo "<tr>";
    echo "<td>V" . htmlspecialchars($i + 1) . "</td>";
    echo "<td>" . htmlspecialchars(number_format($points[$i]['x'], 2, ',', '')) . "</td>";
    echo "<td>" . htmlspecialchars(number_format($points[$i]['y'], 2, ',', '')) . "</td>";
    echo "</tr>";
  }

  pg_free_result($points_result);
} else {
            echo "<tr><td colspan='4'>Error obteniendo puntos: " . pg_last_error($conn) . "</td></tr>";
          }
          ?>
        </tbody>
        <tr>
            <td colspan="3" style="text-align: center;"><h6>Observaciones: Teniendo en cuenta las coordenadas presentas a fs.22, se cumple en informar que se registra la cantera según las coordenadas que anteceden. Se aclara que lo solicitado se encuentra sobre el cateo exp: ................. y dentro (plantas) el Exp: ................. -Queda registrado el pedido en las coordenadas que anteceden.</h6></td>
          </tr>
      </table>
    <?php endwhile; ?>
  <?php elseif ($result): ?>
    <p>No se encontraron resultados para la búsqueda.</p>
  <?php elseif ($busqueda_id || $busqueda_expte): ?>
    <p>Error en la búsqueda: <?= pg_last_error($conn) ?></p>
  <?php endif; ?>

</body>
</html>
<?php
if ($result) pg_free_result($result);
pg_close($conn);
?>
