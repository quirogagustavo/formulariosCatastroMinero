<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

?>
<?php
/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/

include 'conectar_bd.php';

if (!$db) {
    die("Error de conexión a la base de datos.");
}


$reparticion = $_POST['reparticion'] ?? '';
$num_exp = $_POST['num_exp'] ?? '';
$ano = $_POST['ano'] ?? '';
$exp_siged = $_POST['nroexpediente_usado'] ?? '';
$man_nexpte = $reparticion.$num_exp.$ano;
$fecha_alta = $_POST['fecha_alta'] ?? '';
$departamento = $_POST['departamento'] ?? '';
$denominacion = strtoupper($_POST['denominacion']) ?? '';
$minerales_array = $_POST['minerales'] ?? []; 
$programa = $_POST['programa'];
$superficie = $_POST['sup_ha'];
$puntos = json_decode($_POST['puntos'] ?? '[]', true);
$muestra_x = $_POST['muestra_x'];
$muestra_y = $_POST['muestra_y'];
$cuit_array = (array) ($_POST['cuit'] ?? []);
$solicitante_array = (array) ($_POST['solicitante'] ?? []);
$tipo_array = (array) ($_POST['tipo'] ?? []);
$cuit_concat = implode(' | ', array_map(fn($c) => preg_replace('/\D/', '', $c), $cuit_array));
$solicitante_concat = implode(' | ', array_map(fn($s) => strtoupper(trim($s)), $solicitante_array));
$tipo_yacimiento = $_POST['tipo_yacimiento'] ?? '';
$formulario = 'SOLICITUD DE MANIFESTACION DE DESCUBRIMIENTO';


echo '<h2>Los valores a ingresar a la capa serán los siguientes:</h2><br>';
echo 'Expediente: '.$man_nexpte.'<br>';
echo 'Fecha Solicitud: '.$fecha_alta.'<br>';
echo 'Departamento: '.$departamento.'<br>';
echo 'Denominacion: '.$denominacion.'<br>';
echo 'Acompaña Muestra Mineral: '.$programa.'<br>';
echo 'Superficie Solicitada: '.$superficie.'<br>';

if (empty($man_nexpte) || !is_array($puntos) || count($puntos) < 3) {
    die("Datos incompletos o inválidos.");
}

// Aplicar corrección para polígonos de 4 puntos (evitar forma de reloj de arena)
if (count($puntos) === 4) {
    // Reordenar: V3 (índice 2) → V1 (índice 0) → V4 (índice 3) → V2 (índice 1)
    $puntos_corregidos = [
        $puntos[2], // V3 primero
        $puntos[0], // V1 segundo
        $puntos[3], // V4 tercero
        $puntos[1]  // V2 cuarto
    ];
    $puntos = $puntos_corregidos;
    echo '<p style="color: blue;"><strong>Nota:</strong> Se aplicó corrección automática para 4 puntos (orden V3→V1→V4→V2) para evitar forma de reloj de arena.</p>';
}

$coords = array_map(function($p) {
    return "{$p['x']} {$p['y']}";
}, $puntos);


if ($coords[0] !== end($coords)) {
    $coords[] = $coords[0];
}

$wkt = "POLYGON((" . implode(",", $coords) . "))";

if ($programa=='SI') {
$muestra_z = 0;
$muestra_m = 0;

$wkt_punto_muestra = "POINT($muestra_x $muestra_y)";

$res2 = pg_query($db, 'SELECT COALESCE(MAX(lem_id), 0) + 1 AS next_id FROM registro_grafico.gra_cm_lem_pga07');
if (!$res2) {
    die("Error obteniendo el próximo ID: " . pg_last_error($db));
}
$row2 = pg_fetch_assoc($res2);
$muestra_next_id = (int)$row2['next_id'];

echo 'Muestra Este: '.$muestra_x.'<br>';
echo 'Muestra Norte: '.$muestra_y.'<br><br><br>';

}

$res = pg_query($db, 'SELECT COALESCE(MAX(manif_id), 0) + 1 AS next_id FROM registro_grafico.gra_cm_manifestaciones_pga07');
if (!$res) {
    die("Error obteniendo el próximo ID: " . pg_last_error($db));
}
$row = pg_fetch_assoc($res);
$next_id = (int)$row['next_id'];

$query = "
    INSERT INTO registro_grafico.gra_cm_manifestaciones_pga07 
    (manif_id, expte_siged, geom, fecha_solicitud, depto, sup_decl_ha, muestra_min, denom, tipo_yac)
    VALUES (
        $1,
        $2,
        ST_GeomFromText($3, 5344),
        $4,
        $5,
        $6,
        $7,
        $8,
        $9)";

$result = pg_query_params($db, $query, [$next_id, $exp_siged, $wkt, $fecha_alta, $departamento, $superficie,$programa,$denominacion,$tipo_yacimiento]);

if ($result) {
    echo "<h4>Polígono guardado correctamente con ID: ".$next_id." -- en registro_grafico.gra_cm_manifestaciones_pga07  <h4>";
} else {
    echo "<h4>Error al guardar gra_cm_manifestaciones_pga07:<h5>" . pg_last_error($db);
}

if ($programa=='SI') {

$query2 = "
    INSERT INTO registro_grafico.gra_cm_lem_pga07 
    (lem_id, expte_siged, geom, fecha_alta, depto, denom, e_pga07, n_pga07)
    VALUES (
        $1,
        $2,
        ST_GeomFromText($3, 5344),
        $4,
        $5,
        $6,
        $7,
        $8)";

$result2 = pg_query_params($db, $query2, [$muestra_next_id, $exp_siged, $wkt_punto_muestra, $fecha_alta, $departamento, $denominacion, $muestra_x, $muestra_y]);

if ($result2) {
    echo "<h4>Marcador guardado correctamente con ID: ".$muestra_next_id." -- en registro_grafico.gra_cm_lem_pga07 <h4>";
     
    $insert_solicitante = pg_prepare($db, "insert_solic", "INSERT INTO registro_grafico.tbl_solicitantes (expediente, solicitante, cuit, formulario, tipo) VALUES ($1, $2, $3, $4, $5)");
    
    foreach ($solicitante_array as $i => $nombre) {
        $nombre = strtoupper(trim($nombre));
        $cuit = preg_replace('/\D/', '', $cuit_array[$i] ?? '');
        $tipo = $tipo_array[$i];

        echo "[$i] Nombre: $nombre, CUIT: $cuit, TIPO: '$tipo'<br>";
                
        if ($nombre && $cuit) {
            $ok = pg_execute($db, "insert_solic", [$exp_siged, $nombre, $cuit, $formulario, $tipo]);
            if (!$ok) {
                echo "<p style='color:red'>Error insertando solicitante: ".pg_last_error($db)."</p>";
            }
            else {echo "<p style='color:green'>Insertando solicitante en tabla solicitantes </p>";}
        }
    }

     
    $prepMinerales = pg_prepare($db, "insert_mineral", "INSERT INTO registro_grafico.tbl_formulario_minerales (expediente, id_mineral, formulario) VALUES ($1, $2, 'SOLICITUD DE MANIFESTACION DE DESCUBRIMIENTO')");

    if (!$prepMinerales) {
        echo "<p style='color:red'>Error preparando inserción minerales: ".pg_last_error($db)."</p>";
    } else {
        foreach ($minerales_array as $id_mineral) {
            $id_mineral = intval($id_mineral); 
            $ok = pg_execute($db, "insert_mineral", [$exp_siged, $id_mineral]);
            if (!$ok) {
                echo "<p style='color:red'>Error insertando mineral ID $id_mineral: ".pg_last_error($db)."</p>";
            }
            else {echo "<p style='color:green'>Insertando mineral en tabla registro_grafico.tbl_formulario_minerales </p>";}
        }
    }

        $fecha = $_POST['fecha'] ?? date('Y-m-d');
        $estado = 'Aceptado';
        $observaciones = '';
        $query = "INSERT INTO registro_grafico.tbl_operacion_expediente (expediente, fecha, estado, observaciones, formulario) VALUES ($1, $2, $3, $4, $5)";
        $result = pg_query_params($db, $query, [$exp_siged, $fecha, $estado, $observaciones, $formulario]);

        if ($result) {
        echo "<div style='padding: 2rem; font-family: sans-serif;'><h3>✅ Operación registrada correctamente.</h3><a href='menu.php'>Menú Principal</a></div>";
        } else {
        echo "<div style='padding: 2rem; font-family: sans-serif;'><h3 style='color:red;'>❌ Error al guardar.</h3><p>" . pg_last_error($db) . "</p></div>";
        }

} else {
    echo "<h4>Error al guardar:<h5>" . pg_last_error($db);
}
}


?>

