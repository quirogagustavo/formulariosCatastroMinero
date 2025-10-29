<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
$categoria = trim(($_POST['cat1'] ?? '') . ' ' . ($_POST['cat2'] ?? ''));
$programa = $_POST['programa'];
$superficie = $_POST['sup_ha'];
$puntos = json_decode($_POST['puntos'] ?? '[]', true);
$cuit_array = (array) ($_POST['cuit'] ?? []);
$solicitante_array = (array) ($_POST['solicitante'] ?? []);
$tipo_array = (array) ($_POST['tipo'] ?? []);
$cuit_concat = implode(' | ', array_map(fn($c) => preg_replace('/\D/', '', $c), $cuit_array));
$solicitante_concat = implode(' | ', array_map(fn($s) => strtoupper(trim($s)), $solicitante_array));
$formulario = 'SOLICITUD DE PERMISO DE EXPLORACION';

echo '<h2>Los valores a ingresar a la capa serán los siguientes:</h2><br>';
echo 'Expediente: '.$exp_siged.'<br>';
echo 'Fecha Solicitud: '.$fecha_alta.'<br>';
echo 'Departamento: '.$departamento.'<br>';
echo 'Categoria: '.$categoria.'<br>';
echo 'Programa: '.$programa.'<br>';
echo 'Superficie Solicitada: '.$superficie.'<br>';

if (empty($exp_siged) || !is_array($puntos) || count($puntos) < 3) {
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

$res = pg_query($db, 'SELECT COALESCE(MAX(pexp_id), 0) + 1 AS next_id FROM registro_grafico.gra_cm_permisos_exploracion_pga07');
if (!$res) {
    die("Error obteniendo el próximo ID: " . pg_last_error($db));
}
$row = pg_fetch_assoc($res);
$next_id = (int)$row['next_id'];

$query = "
        INSERT INTO registro_grafico.gra_cm_permisos_exploracion_pga07 
        (pexp_id, expte_siged, geom, fecha_solicitud, depto, cat_min, sup_decl_ha, prog_trab)
        VALUES (
            $1,
            $2,
            ST_GeomFromText($3, 5344),
            $4,
            $5,
            $6,
            $7,
            $8)";

$result = pg_query_params($db, $query, [$next_id, $exp_siged, $wkt, $fecha_alta, $departamento, $categoria,$superficie,$programa]);

if ($result) {
    echo "<h4>Polígono guardado correctamente con ID: ".$next_id." -- en registro_grafico.gra_cm_permisos_exploracion_pga07 <h4>";
    
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
    
?>

