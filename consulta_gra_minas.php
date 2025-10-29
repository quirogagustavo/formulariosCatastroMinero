<?php

/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/

include 'conectar_bd.php';

$sql = "SELECT mina_id, ST_AsGeoJSON(ST_Transform(geom, 4326)) AS geojson, denominacion, expediente FROM registro_grafico.vw_minas_padron WHERE geom IS NOT NULL";
$res = pg_query($conn, $sql);
$features = [];

while ($row = pg_fetch_assoc($res)) {
    $geometry = json_decode($row['geojson']);
    $features[] = [
        "type" => "Feature",
        "geometry" => $geometry,
        "properties" => [
            "id" => $row['mina_id'],
            "denominacion" => $row['denominacion'],
            "expediente" => $row['expte_siged']
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
