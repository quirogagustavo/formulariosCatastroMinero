<?php

/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/

include 'conectar_bd.php';

$sql = 'SELECT "ResId", ST_AsGeoJSON(ST_Transform(geom, 4326)) AS geojson FROM registro_grafico.gra_cs_reservas_pga07 WHERE geom IS NOT NULL';

$res = pg_query($conn, $sql);
$features = [];

while ($row = pg_fetch_assoc($res)) {
    $geometry = json_decode($row['geojson']);
    $features[] = [
        "type" => "Feature",
        "geometry" => $geometry,
        "properties" => [
            "id" => $row['ResId'],
            "denominacion" => '',
            "expediente" => ''
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
