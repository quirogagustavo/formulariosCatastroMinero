<?php
include 'conectar_bd.php';

// Obtener parámetro del expediente
$expediente = isset($_GET['expediente']) ? $_GET['expediente'] : '1124-296297-1990-EXP';

// Consulta para obtener las pertenencias en formato GeoJSON
$query = "SELECT jsonb_build_object(
    'type', 'FeatureCollection',
    'features', jsonb_agg(feature)
)
FROM (
    SELECT jsonb_build_object(
        'type', 'Feature',
        'id', mens_id,
        'geometry', ST_AsGeoJSON(geom)::jsonb,
        'properties', jsonb_build_object(
            'expediente', expte_siged,
            'pertenencia_id', id_pert,
            'poligono_id', id_pol,
            'denominacion', denom,
            'sup_declarada_ha', sup_decla_men_ha,
            'sup_registrada_ha', sup_reg_ha,
            'orientacion', CASE 
                WHEN ST_IsPolygonCCW(geom) THEN 'ANTIHORARIO' 
                ELSE 'HORARIO' 
            END,
            'num_vertices', ST_NPoints(geom),
            'valido', ST_IsValid(geom)
        )
    ) AS feature
    FROM registro_grafico.gra_cm_mensura_pertenencias_pga07
    WHERE expte_siged = $1
) features;";

$result = pg_query_params($db, $query, [$expediente]);

if ($result) {
    $row = pg_fetch_row($result);
    $geojson = $row[0];
    
    // Establecer headers para descarga
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="pertenencia_' . str_replace(['/', '-'], '_', $expediente) . '.geojson"');
    
    echo $geojson;
} else {
    header('Content-Type: text/plain');
    echo "Error: " . pg_last_error($db);
}

pg_close($db);
?>
