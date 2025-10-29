<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

include 'conectar_bd.php';

if (!$conn) {
    die("Error de conexión a la base de datos.");
}


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
  <h1>Reporte LABOR LEGAL</h1>
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
        document.getElementById('id').value = <?php echo json_encode($row['ll_id']); ?>;
        document.getElementById('enlace').href = "exportar_denuncia_labor_legal.php?id=<?= urlencode($row['ll_id']) ?>&expte=<?= urlencode($row['expte_siged']) ?>";
         
        
        });
        
        </script>
        <table>
        <thead>
          <tr>
           <td colspan="2" style="text-align: center;"><h2>Labor Legal</h2></td>
          </tr>
          <tr>
            <th>ID:</th>
            <td><?= htmlspecialchars($row['ll_id']) ?></td>
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
            $titular_sql = "SELECT solicitante, cuit FROM registro_grafico.tbl_solicitantes WHERE expediente = $1 AND formulario = 'DENUNCIA DE LABOR LEGAL'";
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
            <th>Nombre de manifestación de descubrimiento:</th>
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
                                AND fm.formulario = 'DENUNCIA DE LABOR LEGAL'";

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
          <tr>
            <th>Tipo de Labor Ejecutada:</th>
            <td><?= htmlspecialchars($row['tipo_labor']) ?></td>
          </tr>
                              
        </thead>
        <tbody>
          
          
        </tbody>
      </table>

      <table class="points-table">
        <thead>
          <tr>
            <td colspan="3" style="text-align: center;"><h3>Ingresar Coordenadas Gauss Krüger Faja 2 POSGAR 2007 (EPSG:5344)</h3></td>
          </tr>
          <tr>
            <th>VERTICES</th>
            <th>ESTE (X)</th>
            <th>NORTE (Y)</th>
          </tr>
        </thead>
        <tbody>
           <td>LABOR LEGAL</td>

           <?php
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
            ?>
           </td>
          </tr>

<?php          
  pg_free_result($points_result);
?>
        </tbody>
        <tr>
            <td colspan="3" style="text-align: center;"><h6>NOTA: por convención el Vértice V1 corresponde al esquinero superior-izquierdo, los siguientes en sentido horario.</h6></td>
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
