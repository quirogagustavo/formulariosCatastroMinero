<?php

/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/

include 'conectar_bd.php';

if (!$conn) {
    die("Error de conexión a la base de datos.");
}

// Traemos los polígonos con sus datos
$sql = 'SELECT * FROM registro_grafico.vw_permisos_exploracion';

$result = pg_query($conn, $sql);
if (!$result) {
    die("Error en la consulta: " . pg_last_error($conn));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Polígonos y sus puntos</title>
  <style>
    table { border-collapse: collapse; width: 100%; margin-bottom: 30px; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
    .points-table { margin-left: 40px; width: 90%; }
  </style>
</head>
<body>
  <h2>Polígonos y atributos</h2>

  <?php while ($row = pg_fetch_assoc($result)): ?>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Expediente</th>
          
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><?php echo htmlspecialchars($row['pexp_id']); ?></td>
          <td><?php echo htmlspecialchars($row['expte_siged']); ?></td>
          
        </tr>
      </tbody>
    </table>

    <strong>Puntos del polígono ID <?php echo htmlspecialchars($row['pexp_id']); ?>:</strong>
    <table class="points-table">
      <thead>
        <tr>
          <th>Punto</th>
          <th>Coordenada X</th>
          <th>Coordenada Y</th>
          
        </tr>
      </thead>
      <tbody>
        <?php
        // Consulta para obtener puntos de este polígono usando ST_DumpPoints
        $pid = (int)$row['pexp_id'];
        $points_sql = "
          SELECT (dp).path[1] as punto_num,
                 ST_X((dp).geom) AS x,
                 ST_Y((dp).geom) AS y
                 
          FROM (
            SELECT ST_DumpPoints(geom) AS dp
            FROM registro_grafico.vw_permisos_exploracion
            WHERE pexp_id = $pid
          ) AS foo
          ORDER BY punto_num
        ";

        $points_result = pg_query($conn, $points_sql);
        if ($points_result) {
          while ($p = pg_fetch_assoc($points_result)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($p['punto_num']) . "</td>";
            echo "<td>" . htmlspecialchars($p['x']) . "</td>";
            echo "<td>" . htmlspecialchars($p['y']) . "</td>";
            //echo "<td>" . htmlspecialchars($p['z']) . "</td>";
            echo "</tr>";
          }
          pg_free_result($points_result);
        } else {
          echo "<tr><td colspan='4'>Error obteniendo puntos: " . pg_last_error($conn) . "</td></tr>";
        }
        ?>
      </tbody>
    </table>

  <?php endwhile; ?>

</body>
</html>
<?php
pg_free_result($result);
pg_close($conn);
?>

