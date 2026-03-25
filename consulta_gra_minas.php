<?php

/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/

include 'conectar_bd.php';

$sql = "SELECT mina_id, ST_AsGeoJSON(ST_Transform(geom, 4326)) AS geojson, denominacion, expediente FROM registro_grafico.vw_minas_padron WHERE geom IS NOT NULL";
$res = pg_query($conn, $sql);

if (!$res) {
    // Devolver error descriptivo en desarrollo y registrar en log
    $pg_error = pg_last_error($conn);
    error_log("Error en consulta_gra_minas: $pg_error. SQL: $sql");
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'mensaje' => 'Error al consultar las minas',
        'detalle' => $pg_error
    ]);
    exit;
}
$features = [];

while ($row = pg_fetch_assoc($res)) {
    $geometry = json_decode($row['geojson']);
    $features[] = [
        "type" => "Feature",
        "geometry" => $geometry,
        "properties" => [
            "id" => $row['mina_id'],
            "denominacion" => $row['denominacion'],
            "expediente" => $row['expediente']
            //"anio" => $row['anio']
        ]
    ];
}

$geojson = [
    "type" => "FeatureCollection",
    "features" => $features
];

header('Content-Type: application/json');
echo json_encode($geojson);
?>
