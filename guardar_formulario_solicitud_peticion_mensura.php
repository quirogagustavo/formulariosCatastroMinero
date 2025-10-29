<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/

include 'conectar_bd.php';
if (!$db) die("Error de conexión a la base de datos.");


$exp_siged    = $_POST['nroexpediente_usado'] ?? '';
if (!$exp_siged) die("Falta el expediente SIGED.");

$fecha_alta   = $_POST['fecha_alta'] ?? date('Y-m-d');
$denominacion = strtoupper($_POST['denominacion']) ?? '';
$departamento = $_POST['departamento'] ?? '';
$superficie_mensura = floatval($_POST['sup_ha'] ?? 0);
$minerales_array = $_POST['minerales'] ?? []; 
$tipo_yacimiento = $_POST['tipo_yacimiento'] ?? '';
$cuit_array = (array) ($_POST['cuit'] ?? []);
$solicitante_array = (array) ($_POST['solicitante'] ?? []);
$tipo_array = (array) ($_POST['tipo'] ?? []);
$cuit_concat = implode(' | ', array_map(fn($c) => preg_replace('/\D/', '', $c), $cuit_array));
$solicitante_concat = implode(' | ', array_map(fn($s) => strtoupper(trim($s)), $solicitante_array));
$solicitudesJSON = $_POST['solicitudes_mensura'] ?? '[]';
$solicitudes = json_decode($solicitudesJSON,true);
$formulario = 'SOLICITUD DE PETICION DE MENSURA';

if(!$solicitudes){
    die("❌ No se recibieron solicitudes de mensura válidas");
}

$multipoligonosJSON = $_POST['multipoligonos'] ?? '[]';
$multipoligonos = json_decode($multipoligonosJSON,true);
if(!$multipoligonos){
    die("❌ No se recibieron pertenencias válidas");
}


function next_id($db, $tabla, $campo){
  $res = pg_query($db,"SELECT COALESCE(MAX($campo),0)+1 AS id FROM $tabla");
  $row = pg_fetch_assoc($res);
  return (int)$row['id'];
}


// ------------------- INSERTAR MENSURAS -------------------
foreach($solicitudes as $s){
    $mensura_id = next_id($db,'registro_grafico.gra_cm_mensura_area_pga07','mensar_id');

    // WKT
    $coords = array_map(fn($v)=>[$v['x'],$v['y']], $s['vertices'] ?? []);
    if(count($coords) < 3) { echo "❌ Mensura sin vértices suficientes<br>"; continue; }
    if($coords[0] !== end($coords)) $coords[] = $coords[0];
    $coords_wkt = array_map(fn($c)=>implode(' ',$c), $coords);
    $wkt = "POLYGON((" . implode(',', $coords_wkt) . "))";

    $sup_graf_ha = isset($s['sup_graf_ha']) ? floatval($s['sup_graf_ha']) : 0;
    $sup_decl    = isset($s['sup_decl'])    ? floatval($s['sup_decl'])    : 0;
    $id_pol    =  $s['id_mensura'];

    $q = "INSERT INTO registro_grafico.gra_cm_mensura_area_pga07
            (mensar_id, expte_siged, fecha_solicitud, depto, tipo_yac, geom, sup_graf_ha, sup_decla_ha, denom, sup_decla_men_ha, id_pol)
          VALUES ($1,$2,$3,$4,$5, ST_GeomFromText($6,5344), $7, $8, $9, $10, $11)";

    $p = [
        $mensura_id,
        $exp_siged ?? '',
        $fecha_alta ?? date('Y-m-d'),
        $departamento ?? '',
        $tipo_yacimiento ?? '',
        $wkt,
        $sup_graf_ha,
        $sup_decl,
        $denominacion,
        $superficie_mensura,
        $id_pol
    ];

    $res = pg_query_params($db,$q,$p);
    echo $res ? "✔ Mensura $mensura_id guardada<br>" : "❌ Error mensura: ".pg_last_error($db)."<br>";
}


// ------------------- INSERTAR PERTENENCIAS -------------------
foreach($multipoligonos as $pert){
    $pert_id = next_id($db,'registro_grafico.gra_cm_mensura_pertenencias_pga07','mens_id');
    $id_sol = $pert['id_sol'] ?? '';
    $id_pert = $pert['id_p'] ?? '';
    $sup_graf_ha = isset($pert['sup_graf_ha']) ? floatval($pert['sup_graf_ha']) : 0;
    
    $sup_decl    = isset($pert['sup_decl'])    ? floatval($pert['sup_decl'])    : 0;

    $coords = array_map(fn($v)=>[$v['x'],$v['y']], $pert['vertices']);
    if($coords[0] !== end($coords)) $coords[] = $coords[0]; // cerrar polígono
    $coords_wkt = array_map(fn($c)=>implode(' ',$c), $coords);
    $wkt = "POLYGON((" . implode(',', $coords_wkt) . "))";

    $q = "INSERT INTO registro_grafico.gra_cm_mensura_pertenencias_pga07
            (mens_id, id_pol, id_pert, geom, sup_graf_ha, sup_solic_ha, denom, sup_decla_men_ha, tipo_yac, expte_siged)
          VALUES ($1,$2,$3, ST_GeomFromText($4,5344), $5, $6, $7, $8, $9, $10)";

    $p = [$pert_id,$id_sol,$id_pert,$wkt,$sup_graf_ha,$sup_decl,$denominacion, $superficie_mensura, $tipo_yacimiento, $exp_siged];

    $res = pg_query_params($db,$q,$p);
    echo $res ? "✔ Pertenencia $pert_id guardada<br>" : "❌ Error pertenencia: ".pg_last_error($db)."<br>";
}


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

    $prepMinerales = pg_prepare($db, "insert_mineral", "INSERT INTO registro_grafico.tbl_formulario_minerales (expediente, id_mineral, formulario) VALUES ($1, $2, 'SOLICITUD DE PETICION DE MENSURA')");

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



echo "<h4>Proceso finalizado.</h4>";
?>
