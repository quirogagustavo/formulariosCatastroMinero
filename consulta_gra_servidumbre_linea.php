<?php

/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/

include 'conectar_bd.php';

// Asegurate de que el SRID sea correcto (EPSG:22182)
$sql = "SELECT serv_lin_id, ST_AsGeoJSON(ST_Transform(geom, 4326)) as geojson, expte_siged FROM registro_grafico.vw_servidumbres_lineas WHERE geom IS NOT NULL";

$res = pg_query($conn, $sql);
$features = [];

while ($row = pg_fetch_assoc($res)) {
    $geometry = json_decode($row['geojson']);
    $features[] = [
        "type" => "Feature",
        "geometry" => $geometry,
        "properties" => [
            "id" => $row['serv_lin_id'],
            "denominacion" => '',
            "expediente" => $row['expte_siged']
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
