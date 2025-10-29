<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configuración DB
include 'conectar_bd.php';

if (!$db) {
    die("Error de conexión a la base de datos.");
}

// Datos del formulario
$reparticion = $_POST['reparticion'] ?? '';
$num_exp = $_POST['num_exp'] ?? '';
$ano = $_POST['ano'] ?? '';

$exp_siged = $_POST['nroexpediente_usado'] ?? '';

$man_nexpte = $reparticion.$num_exp.$ano;

$fecha_alta = $_POST['fecha_alta'] ?? '';
$departamento = $_POST['departamento'] ?? '';
$cantera = $_POST['cantera'] ?? '';


$proyecto = $_POST['proyecto'];
$zona = $_POST['zona'];

$superficie = $_POST['sup_ha'];
$duracion = $_POST['duracion'];
$puntos = json_decode($_POST['puntos'] ?? '[]', true);

$cuit_array = (array) ($_POST['cuit'] ?? []);
$solicitante_array = (array) ($_POST['solicitante'] ?? []);
$tipo_array = (array) ($_POST['tipo'] ?? []);

$cuit_concat = implode(' | ', array_map(fn($c) => preg_replace('/\D/', '', $c), $cuit_array));
$solicitante_concat = implode(' | ', array_map(fn($s) => strtoupper(trim($s)), $solicitante_array));

$minerales_array = $_POST['minerales'] ?? []; // Array con ids de minerales seleccionados
$formulario = 'SOLICITUD DE CANTERAS';

echo '<h2>Los valores a ingresar a la capa serán los siguientes:</h2><br>';
echo 'Expediente: '.$exp_siged.'<br>';
echo 'Fecha Solicitud: '.$fecha_alta.'<br>';
echo 'Departamento: '.$departamento.'<br>';
echo 'Nombre Canteria: '.$cantera.'<br>';
echo 'Proyecto: '.$proyecto.'<br>';
echo 'Zona: '.$zona.'<br>';
echo 'Duracion Dias: '.$duracion.'<br>';
echo 'Superficie Declarada: '.$superficie.'<br>';

if (empty($exp_siged) || !is_array($puntos) || count($puntos) < 3) {
    die("Datos incompletos o inválidos.");
}


$coords = array_map(function($p) {

    return "{$p['x']} {$p['y']}";
}, $puntos);


if ($coords[0] !== end($coords)) {
    $coords[] = $coords[0];
}


$wkt = "POLYGON((" . implode(",", $coords) . "))";


$res = pg_query($db, 'SELECT COALESCE(MAX(cant_id), 0) + 1 AS next_id FROM registro_grafico.gra_cm_canteras_pga07');
if (!$res) {
    die("Error obteniendo el próximo ID: " . pg_last_error($db));
}
$row = pg_fetch_assoc($res);
$next_id = (int)$row['next_id'];


$query = "
        INSERT INTO registro_grafico.gra_cm_canteras_pga07
        (cant_id, expte_siged, geom, fecha_solicitud, depto, denom, sup_decl_ha, aprovechamiento, sit_zona, tiempo_concesion)
        VALUES (
            $1,
            $2,
            ST_GeomFromText($3, 5344),
            $4,
            $5,
            $6,
            $7,
            $8,
            $9,
            $10)";


$result = pg_query_params($db, $query, [$next_id, $exp_siged, $wkt, $fecha_alta, $departamento, $cantera, $superficie, $proyecto, $zona, $duracion]);

if ($result) {
    echo "<h4>Polígono guardado correctamente con ID: ".$next_id." -- en registro_grafico.gra_cm_canteras_pga07 <h4>";

    $insert_solicitante = pg_prepare($db, "insert_solic", "INSERT INTO registro_grafico.tbl_solicitantes (expediente, solicitante, cuit, formulario, tipo) VALUES ($1, $2, $3, $4, $5)");
    
    foreach ($solicitante_array as $i => $nombre) {
        $nombre = strtoupper(trim($nombre));
        $cuit = preg_replace('/\D/', '', $cuit_array[$i] ?? '');
        $tipo = $tipo_array[$i];

                       
        if ($nombre && $cuit) {
            $ok = pg_execute($db, "insert_solic", [$exp_siged, $nombre, $cuit, $formulario, $tipo]);
            if (!$ok) {
                echo "<p style='color:red'>Error insertando solicitante: ".pg_last_error($db)."</p>";
            }
            else {echo "<p style='color:green'>Insertando solicitante en tabla solicitantes </p>";}
        }
    }

        
    $prepMinerales = pg_prepare($db, "insert_mineral", "INSERT INTO registro_grafico.tbl_formulario_minerales (expediente, id_mineral, formulario) VALUES ($1, $2, 'SOLICITUD DE CANTERAS')");

    if (!$prepMinerales) {
        echo "<p style='color:red'>Error preparando inserción minerales: ".pg_last_error($db)."</p>";
    } else {
        foreach ($minerales_array as $id_mineral) {
            $id_mineral = intval($id_mineral); // seguridad básica
            $ok = pg_execute($db, "insert_mineral", [$exp_siged, $id_mineral]);
            if (!$ok) {
                echo "<p style='color:red'>Error insertando mineral ID $id_mineral: ".pg_last_error($db)."</p>";
            }
            else {echo "<p style='color:green'>Insertando mineral en tabla formulario_minerales </p>";}
        }
    }

        $fecha = $_POST['fecha'] ?? date('Y-m-d');
        $estado = 'Aceptado';
        $observaciones = '';
        $query = "INSERT INTO registro_grafico.tbl_operacion_expediente  (expediente, fecha, estado, observaciones, formulario) VALUES ($1, $2, $3, $4, $5)";
        $result = pg_query_params($db, $query, [$exp_siged, $fecha, $estado, $observaciones, $formulario]);

        if ($result) {
        echo "<div style='padding: 2rem; font-family: sans-serif;'><h3>✅ Operación registrada correctamente.</h3><a href='menu.php'>Menú Principal</a></div>";
        } else {
        echo "<div style='padding: 2rem; font-family: sans-serif;'><h3 style='color:red;'>❌ Error al guardar.</h3><p>" . pg_last_error($db) . "</p></div>";
        }

} else {
    echo "<h4>Error al guardar:<h5>" . pg_last_error($db);
}
    
?>

