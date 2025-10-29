<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
  <h1>Reporte SERVIDUMBRE</h1>
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
        document.getElementById('enlace').href = "exportar_denuncia_servidumbre.php?expte=<?= urlencode($row['expte_siged']) ?>";
        });
        
        </script>
        <table>
        <thead>
          <tr>
           <td colspan="2" style="text-align: center;"><h2>SERVIDUMBRE</h2></td>
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
            $titular_sql = "SELECT solicitante, cuit FROM registro_grafico.tbl_solicitantes WHERE expediente = $1 AND formulario = 'DENUNCIA DE SERVIDUMBRE'";
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
            <th>Derechos a los que sirve:</th>
            <td><?= htmlspecialchars($row['derecho']) ?></td>
          </tr>
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
        echo "<h3>Servidumbres LINEA</h3>";
        while ($row1 = pg_fetch_assoc($result1)) {
            echo "Expte: " . htmlspecialchars($row1['expte_siged']) . 
                 " | ID: " . htmlspecialchars($row1['serv_lin_id']) . 
                 " | Departamento: " . htmlspecialchars($row1['depto']) .
                 " | Tipo Servidumbre: " . htmlspecialchars($row1['tipo_servidumbre']) . 
                 " | Objeto Servidumbre: " . htmlspecialchars($row1['objeto_servidumbre']) . 
                 " | Ancho Declarado Camino: " . $row1['ancho_servidumbre'] . 
                 " | Superficie Registrada Total: " . $row1['sup_graf_ha'] . "<br><br>";
            $vertices = json_decode($row1['vertices'], true);
            foreach ($vertices as $v) {
                echo "&nbsp;&nbsp;&nbsp;Vertice: (" . $v['x'] . ", " . $v['y'] . ")<br>";
            }
        echo "<br>";
        }
        
        pg_free_result($result1);
    } else {
        echo "Error en consulta LINEA: " . pg_last_error($conn);
    }

    if ($result2) {
        echo "<h3>Servidumbres POLÍGONO</h3>";
        while ($row2 = pg_fetch_assoc($result2)) {
            echo "Expte: " . htmlspecialchars($row2['expte_siged']) . 
                 " | ID: " . htmlspecialchars($row2['serv_pol_id']) . 
                 " | Departamento: " . htmlspecialchars($row2['depto']) .
                 " | Tipo Servidumbre: " . htmlspecialchars($row2['tipo_servidumbre']) . 
                 " | Objeto Servidumbre: " . htmlspecialchars($row2['objeto_servidumbre']) . 
                 " | Superficie Registrada Total: " . $row2['sup_graf_ha'] . "<br><br>";
            $vertices = json_decode($row2['vertices'], true);
            foreach ($vertices as $v) {
                echo "&nbsp;&nbsp;&nbsp;Vertice: (" . $v['x'] . ", " . $v['y'] . ")<br>";
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
