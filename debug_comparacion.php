<?php
include 'conectar_bd.php';

if (!$conn) {
    die("Error de conexión a la base de datos.");
}

$expediente = '1124-000253-2013-EXP';
$params = array('%' . $expediente . '%');

echo "<h2>COMPARACIÓN DE CONSULTAS</h2>";

// Consulta del REPORTE
echo "<h3>1. CONSULTA DEL REPORTE:</h3>";
$sql_reporte = "
    SELECT 
        t.expte_siged,
        t.id_pol,
        t.sup_reg_ha,
        t.sup_decla_men_ha,
        t.mens_id,
        t.id_pert,
        t.geom AS geom_original,
        json_agg(json_build_object('x', ST_X((dp).geom), 'y', ST_Y((dp).geom))) AS vertices
    FROM registro_grafico.gra_cm_mensura_pertenencias_pga07 t
    JOIN LATERAL ST_DumpPoints(t.geom) AS dp ON true
    WHERE t.expte_siged ILIKE $1
    GROUP BY t.expte_siged, t.id_pol, t.sup_reg_ha, t.sup_decla_men_ha, t.mens_id, t.id_pert, t.geom
    ORDER BY t.id_pert::int ASC
";

$result_reporte = pg_query_params($conn, $sql_reporte, $params);
$count_reporte = 0;
echo "<table border='1'><tr><th>ID Pert</th><th>Superficie</th></tr>";
while ($row = pg_fetch_assoc($result_reporte)) {
    $count_reporte++;
    echo "<tr><td>" . $row['id_pert'] . "</td><td>" . $row['sup_reg_ha'] . "</td></tr>";
}
echo "</table>";
echo "<p><strong>Total reporte: $count_reporte</strong></p>";

// Consulta de EXPORTACIÓN
echo "<h3>2. CONSULTA DE EXPORTACIÓN:</h3>";
$sql_export = "
    SELECT 
        t.expte_siged,
        t.id_pol,
        t.sup_reg_ha,
        t.sup_decla_men_ha,
        t.mens_id,
        t.id_pert,
        t.geom AS geom_original,
        json_agg(json_build_object('x', ST_X((dp).geom), 'y', ST_Y((dp).geom))) AS vertices
    FROM registro_grafico.gra_cm_mensura_pertenencias_pga07 t
    JOIN LATERAL ST_DumpPoints(t.geom) AS dp ON true
    WHERE t.expte_siged ILIKE $1
    GROUP BY t.expte_siged, t.id_pol, t.sup_reg_ha, t.sup_decla_men_ha, t.mens_id, t.id_pert, t.geom
    ORDER BY t.id_pert::int ASC
";

$result_export = pg_query_params($conn, $sql_export, $params);
$count_export = 0;
echo "<table border='1'><tr><th>ID Pert</th><th>Superficie</th></tr>";
while ($row = pg_fetch_assoc($result_export)) {
    $count_export++;
    echo "<tr><td>" . $row['id_pert'] . "</td><td>" . $row['sup_reg_ha'] . "</td></tr>";
}
echo "</table>";
echo "<p><strong>Total exportación: $count_export</strong></p>";

// Buscar pertenencias que falten en exportación
echo "<h3>3. ANÁLISIS DE DIFERENCIAS:</h3>";
if ($count_reporte != $count_export) {
    echo "<p style='color: red;'><strong>¡DIFERENCIA ENCONTRADA!</strong></p>";
    echo "<p>Reporte: $count_reporte vs Exportación: $count_export</p>";
} else {
    echo "<p style='color: green;'>Las consultas devuelven la misma cantidad de registros.</p>";
}

// Buscar pertenencias con problemas de geometría
echo "<h3>4. PERTENENCIAS CON PROBLEMAS:</h3>";
$sql_problemas = "
    SELECT 
        id_pert,
        ST_IsValid(geom) as geom_valida,
        ST_NumPoints(geom) as num_puntos,
        ST_GeometryType(geom) as tipo_geom
    FROM registro_grafico.gra_cm_mensura_pertenencias_pga07 
    WHERE expte_siged ILIKE $1
    AND (ST_IsValid(geom) = false OR ST_NumPoints(geom) < 3)
    ORDER BY id_pert::int ASC
";

$result_problemas = pg_query_params($conn, $sql_problemas, $params);
echo "<table border='1'><tr><th>ID Pert</th><th>Geom Válida</th><th>Num Puntos</th><th>Tipo</th></tr>";
$hay_problemas = false;
while ($row = pg_fetch_assoc($result_problemas)) {
    $hay_problemas = true;
    echo "<tr>";
    echo "<td>" . $row['id_pert'] . "</td>";
    echo "<td>" . ($row['geom_valida'] == 't' ? 'Sí' : 'No') . "</td>";
    echo "<td>" . $row['num_puntos'] . "</td>";
    echo "<td>" . $row['tipo_geom'] . "</td>";
    echo "</tr>";
}
if (!$hay_problemas) {
    echo "<tr><td colspan='4'>No se encontraron problemas de geometría</td></tr>";
}
echo "</table>";

pg_close($conn);
?>