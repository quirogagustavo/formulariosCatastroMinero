<?php
include 'conectar_bd.php';

if (!$conn) {
    die("Error de conexión a la base de datos.");
}

$expediente = '1124-000253-2013-EXP';
echo "<h2>ANÁLISIS EXHAUSTIVO - Expediente: $expediente</h2>";

// 1. Consulta básica sin JOIN para ver todas las pertenencias
echo "<h3>1. TODAS LAS PERTENENCIAS EN LA TABLA (sin condiciones):</h3>";
$sql_base = "SELECT id_pert, id_pol, sup_reg_ha, ST_IsValid(geom) as geom_valida, ST_NumPoints(geom) as num_puntos 
             FROM registro_grafico.gra_cm_mensura_pertenencias_pga07 
             WHERE expte_siged ILIKE $1 
             ORDER BY id_pert::int ASC";

$result_base = pg_query_params($conn, $sql_base, ['%' . $expediente . '%']);
$count_base = 0;

echo "<table border='1'><tr><th>ID Pert</th><th>ID Pol</th><th>Superficie</th><th>Geom Válida</th><th>Num Puntos</th></tr>";
while ($row = pg_fetch_assoc($result_base)) {
    $count_base++;
    echo "<tr>";
    echo "<td>" . $row['id_pert'] . "</td>";
    echo "<td>" . $row['id_pol'] . "</td>";
    echo "<td>" . $row['sup_reg_ha'] . "</td>";
    echo "<td>" . ($row['geom_valida'] == 't' ? 'Sí' : 'No') . "</td>";
    echo "<td>" . $row['num_puntos'] . "</td>";
    echo "</tr>";
}
echo "</table>";
echo "<p><strong>Total en tabla base: $count_base</strong></p>";

// 2. Consulta con JOIN LATERAL para ver qué pasa
echo "<h3>2. CON ST_DumpPoints (como en exportación):</h3>";
$sql_dump = "SELECT DISTINCT t.id_pert, t.id_pol, t.sup_reg_ha
             FROM registro_grafico.gra_cm_mensura_pertenencias_pga07 t
             JOIN LATERAL ST_DumpPoints(t.geom) AS dp ON true
             WHERE t.expte_siged ILIKE $1
             ORDER BY t.id_pert::int ASC";

$result_dump = pg_query_params($conn, $sql_dump, ['%' . $expediente . '%']);
$count_dump = 0;

echo "<table border='1'><tr><th>ID Pert</th><th>ID Pol</th><th>Superficie</th></tr>";
while ($row = pg_fetch_assoc($result_dump)) {
    $count_dump++;
    echo "<tr>";
    echo "<td>" . $row['id_pert'] . "</td>";
    echo "<td>" . $row['id_pol'] . "</td>";
    echo "<td>" . $row['sup_reg_ha'] . "</td>";
    echo "</tr>";
}
echo "</table>";
echo "<p><strong>Total con ST_DumpPoints: $count_dump</strong></p>";

if ($count_base != $count_dump) {
    echo "<p style='color: red;'><strong>¡AQUÍ ESTÁ EL PROBLEMA! ST_DumpPoints está eliminando " . ($count_base - $count_dump) . " pertenencia(s)</strong></p>";
}

// 3. Buscar pertenencias que fallan con ST_DumpPoints
echo "<h3>3. PERTENENCIAS QUE FALLAN CON ST_DumpPoints:</h3>";
$sql_problemas = "SELECT id_pert, id_pol, ST_IsValid(geom) as geom_valida, ST_GeometryType(geom) as tipo_geom, ST_AsText(geom) as geom_text
                  FROM registro_grafico.gra_cm_mensura_pertenencias_pga07 
                  WHERE expte_siged ILIKE $1
                  AND id_pert NOT IN (
                      SELECT DISTINCT t.id_pert
                      FROM registro_grafico.gra_cm_mensura_pertenencias_pga07 t
                      JOIN LATERAL ST_DumpPoints(t.geom) AS dp ON true
                      WHERE t.expte_siged ILIKE $1
                  )
                  ORDER BY id_pert::int ASC";

$result_problemas = pg_query_params($conn, $sql_problemas, ['%' . $expediente . '%', '%' . $expediente . '%']);

echo "<table border='1'><tr><th>ID Pert</th><th>ID Pol</th><th>Geom Válida</th><th>Tipo Geom</th><th>Geometría</th></tr>";
$hay_problemas = false;
while ($row = pg_fetch_assoc($result_problemas)) {
    $hay_problemas = true;
    echo "<tr>";
    echo "<td style='background-color: yellow;'>" . $row['id_pert'] . "</td>";
    echo "<td>" . $row['id_pol'] . "</td>";
    echo "<td>" . ($row['geom_valida'] == 't' ? 'Sí' : 'No') . "</td>";
    echo "<td>" . $row['tipo_geom'] . "</td>";
    echo "<td>" . substr($row['geom_text'], 0, 100) . "...</td>";
    echo "</tr>";
}
if (!$hay_problemas) {
    echo "<tr><td colspan='5'>Todas las pertenencias pasan ST_DumpPoints</td></tr>";
}
echo "</table>";

// 4. Simulación exacta de la consulta de exportación
echo "<h3>4. SIMULACIÓN EXACTA DE CONSULTA DE EXPORTACIÓN:</h3>";
$params = array('%' . $expediente . '%');

$sql_exacta = "
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

$result_exacta = pg_query_params($conn, $sql_exacta, $params);
$count_exacta = 0;

echo "<table border='1'><tr><th>ID Pert</th><th>Superficie</th><th>JSON Válido</th><th>Num Vértices</th></tr>";
while ($row = pg_fetch_assoc($result_exacta)) {
    $count_exacta++;
    $vertices = json_decode($row['vertices'], true);
    $json_valido = ($vertices !== null && is_array($vertices)) ? "Sí" : "No";
    $num_vertices = is_array($vertices) ? count($vertices) : 0;
    
    echo "<tr>";
    echo "<td>" . $row['id_pert'] . "</td>";
    echo "<td>" . $row['sup_reg_ha'] . "</td>";
    echo "<td>" . $json_valido . "</td>";
    echo "<td>" . $num_vertices . "</td>";
    echo "</tr>";
}
echo "</table>";
echo "<p><strong>Total en consulta exacta de exportación: $count_exacta</strong></p>";

echo "<hr>";
echo "<h3>RESUMEN:</h3>";
echo "<ul>";
echo "<li>Pertenencias en tabla base: <strong>$count_base</strong></li>";
echo "<li>Con ST_DumpPoints: <strong>$count_dump</strong></li>";
echo "<li>En consulta de exportación: <strong>$count_exacta</strong></li>";
echo "</ul>";

if ($count_base > $count_exacta) {
    echo "<p style='color: red; font-size: 18px;'><strong>PERTENENCIAS PERDIDAS: " . ($count_base - $count_exacta) . "</strong></p>";
}

pg_close($conn);
?>