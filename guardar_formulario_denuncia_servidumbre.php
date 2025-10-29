<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Conexión DB
include 'conectar_bd.php';

if (!$db) die("Error de conexión a la base de datos.");

// Datos del formulario
$reparticion = $_POST['reparticion'] ?? '';
$num_exp = $_POST['num_exp'] ?? '';
$ano = $_POST['ano'] ?? '';
$exp_siged = $_POST['nroexpediente_usado'] ?? '';

$fecha_alta = $_POST['fecha_alta'] ?? '';
$derechos = $_POST['derechos'] ?? '';

$tipo_servidumbre_entidad   = (array) ($_POST['tipo_servidumbre_entidad'] ?? []);
$objeto_servidumbre_entidad = (array) ($_POST['objeto_servidumbre_entidad'] ?? []);
$departamento_entidad = (array) ($_POST['departamento_entidad'] ?? []);

$ancho_entidad              = (array) ($_POST['ancho_servidumbre_entidad'] ?? []);
$superficie_entidad         = (array) ($_POST['sup_graf_ha_entidad'] ?? []);


$cuit_array = (array) ($_POST['cuit'] ?? []);
$solicitante_array = (array) ($_POST['solicitante'] ?? []);
$tipo_array = (array) ($_POST['tipo'] ?? []);

// Lineas y poligonos del DXF
$lineas = json_decode($_POST['dxf_lineas'] ?? '[]', true);
$poligonos = json_decode($_POST['dxf_poligonos'] ?? '[]', true);

if (!$exp_siged) die("Falta el expediente SIGED.");

// Función para obtener próximo ID
function next_id($db, $tabla, $campo_id) {
    $res = pg_query($db, "SELECT COALESCE(MAX($campo_id),0)+1 AS next_id FROM $tabla");
    if (!$res) die("Error obteniendo ID: ".pg_last_error($db));
    $row = pg_fetch_assoc($res);
    return (int)$row['next_id'];
}

// Preparar insert solicitantes
pg_prepare($db, "insert_solic", "INSERT INTO registro_grafico.tbl_solicitantes (expediente, solicitante, cuit, formulario, tipo) VALUES ($1,$2,$3,$4,$5)");
$formulario = 'DENUNCIA DE SERVIDUMBRE';

// Insertar solicitantes
foreach ($solicitante_array as $i => $nombre) {
    $nombre = strtoupper(trim($nombre));
    $cuit = preg_replace('/\D/', '', $cuit_array[$i] ?? '');
    $tipo = $tipo_array[$i] ?? '';
    if ($nombre && $cuit) {
        $ok = pg_execute($db, "insert_solic", [$exp_siged, $nombre, $cuit, $formulario, $tipo]);
        echo $ok ? "<p>Solicitante $nombre insertado.</p>" : "<p style='color:red'>Error insertando $nombre: ".pg_last_error($db)."</p>";
    }
}

// ====================== INSERTAR LINEAS ======================
foreach ($lineas as $i => $f) {
    if (!isset($f['geometry']['coordinates'])) continue;

    // Armar un LINESTRING
    $coords = array_map(fn($c) => implode(' ', $c), $f['geometry']['coordinates']);
    $wkt = "LINESTRING(" . implode(',', $coords) . ")";

    $tipo_serv = $tipo_servidumbre_entidad[$i] ?? null;
    $objeto    = $objeto_servidumbre_entidad[$i] ?? null;
    $departamento = $departamento_entidad[$i] ?? null;
    $ancho     = floatval($ancho_entidad[$i] ?? 0);
    $sup_calc  = ($ancho * turf_length_php($f)) / 10000;

    $next_id = next_id($db, 'registro_grafico.gra_cm_servidumbres_lin_pga07', 'serv_lin_id');
    $query = "INSERT INTO registro_grafico.gra_cm_servidumbres_lin_pga07 
              (serv_lin_id, expte_siged, geom, fecha_solicitud, depto, tipo_servidumbre, objeto_servidumbre, ancho_servidumbre, sup_graf_ha, derecho)
              VALUES ($1, $2, ST_SetSRID(ST_LineMerge(ST_GeomFromText($3)),5344), $4, $5, $6, $7, $8, $9, $10)";
    
    $params = [$next_id, $exp_siged, $wkt, $fecha_alta, $departamento, $tipo_serv, $objeto, $ancho, $sup_calc, $derechos];
    $res = pg_query_params($db, $query, $params);
    echo $res ? "<p>Línea ID $next_id guardada.</p>" : "<p style='color:red'>Error línea: ".pg_last_error($db)."</p>";
}

// ====================== INSERTAR POLIGONOS ======================
foreach ($poligonos as $j => $f) {
    if (!isset($f['geometry']['coordinates'])) continue;

    $coords = $f['geometry']['coordinates'][0]; // primer anillo
    if ($coords[0] !== end($coords)) {
        $coords[] = $coords[0]; // cerrar polígono
    }
    $coords_wkt = array_map(fn($c) => implode(' ', $c), $coords);
    $wkt = "POLYGON((" . implode(',', $coords_wkt) . "))";

    $tipo_serv = $tipo_servidumbre_entidad[count($lineas) + $j] ?? null;
    $objeto    = $objeto_servidumbre_entidad[count($lineas) + $j] ?? null;
    $departamento = $departamento_entidad[$j] ?? null;
    $sup_ha    = floatval($superficie_entidad[$j] ?? 0);

    $next_id = next_id($db, 'registro_grafico.gra_cm_servidumbres_pol_pga07', 'serv_pol_id');
    $query = "INSERT INTO registro_grafico.gra_cm_servidumbres_pol_pga07 
              (serv_pol_id, expte_siged, geom, fecha_solicitud, depto, tipo_servidumbre, objeto_servidumbre, sup_graf_ha, derecho)
              VALUES ($1, $2, ST_SetSRID(ST_GeometryN(ST_GeomFromText($3),1),5344), $4, $5, $6, $7, $8, $9)";
    
    $params = [$next_id, $exp_siged, $wkt, $fecha_alta, $departamento, $tipo_serv, $objeto, $sup_ha, $derechos];
    $res = pg_query_params($db, $query, $params);
    echo $res ? "<p>Polígono ID $next_id guardado.</p>" : "<p style='color:red'>Error polígono: ".pg_last_error($db)."</p>";
}

// Insertar en la tabla operacion_expediente
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

function distance_haversine($lon1, $lat1, $lon2, $lat2) {
    $R = 6371000; // radio de la Tierra en metros
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat/2) * sin($dLat/2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon/2) * sin($dLon/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    return $R * $c;
}

function turf_length_php($feature) {
    $coords = $feature['geometry']['coordinates'] ?? [];
    $len = 0;
    for ($i=1; $i<count($coords); $i++) {
        $len += distance_haversine(
            $coords[$i-1][0], $coords[$i-1][1],
            $coords[$i][0],   $coords[$i][1]
        );
    }
    return $len; // en metros
}
?>