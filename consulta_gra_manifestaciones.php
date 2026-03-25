<?php

/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/

include 'conectar_bd.php';

$sql = "SELECT manif_id, ST_AsGeoJSON(ST_Transform(geom, 4326)) AS geojson, denom, expediente FROM registro_grafico.vw_manifestaciones_padron WHERE geom IS NOT NULL";
$res = pg_query($conn, $sql);

if (!$res) {
    $pg_error = pg_last_error($conn);
    error_log("Error en consulta_gra_manifestaciones: $pg_error. SQL: $sql");
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'mensaje' => 'Error al consultar las manifestaciones',
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
            "id" => $row['manif_id'],
            "denominacion" => $row['denom'],
            "expediente" => $row['expediente']
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
