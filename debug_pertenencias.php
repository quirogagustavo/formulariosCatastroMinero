<?php
include 'conectar_bd.php';

if (!$conn) {
    die("Error de conexión a la base de datos.");
}

$expediente = '1124-000253-2013-EXP';

echo "<h2>DEBUG: Análisis de pertenencias para expediente: $expediente</h2>";

// Consulta 1: Contar total de pertenencias
$sql_count = "SELECT COUNT(*) as total FROM registro_grafico.gra_cm_mensura_pertenencias_pga07 WHERE expte_siged ILIKE $1";
$result_count = pg_query_params($conn, $sql_count, ['%' . $expediente . '%']);
$count_row = pg_fetch_assoc($result_count);
echo "<p><strong>Total de pertenencias en BD:</strong> " . $count_row['total'] . "</p>";

// Consulta 2: Listar todas las pertenencias con detalles
$sql_list = "SELECT id_pert, id_pol, sup_reg_ha, ST_IsValid(geom) as geom_valida, ST_GeometryType(geom) as tipo_geom 
             FROM registro_grafico.gra_cm_mensura_pertenencias_pga07 
             WHERE expte_siged ILIKE $1 
             ORDER BY id_pert::int ASC";
$result_list = pg_query_params($conn, $sql_list, ['%' . $expediente . '%']);

echo "<h3>Listado de pertenencias:</h3>";
echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>ID Pertenencia</th><th>ID Polígono</th><th>Superficie (ha)</th><th>Geometría Válida</th><th>Tipo Geometría</th></tr>";

while ($row = pg_fetch_assoc($result_list)) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['id_pert']) . "</td>";
    echo "<td>" . htmlspecialchars($row['id_pol']) . "</td>";
    echo "<td>" . htmlspecialchars($row['sup_reg_ha']) . "</td>";
    echo "<td>" . ($row['geom_valida'] == 't' ? 'Sí' : 'No') . "</td>";
    echo "<td>" . htmlspecialchars($row['tipo_geom']) . "</td>";
    echo "</tr>";
}
echo "</table>";

// Consulta 3: Misma consulta que usa la exportación
echo "<h3>Consulta de exportación (con vertices):</h3>";
$sql_export = "
    SELECT 
        t.expte_siged,
        t.id_pol,
        t.sup_reg_ha,
        t.sup_decla_men_ha,
        t.mens_id,
        t.id_pert,
        json_agg(json_build_object('x', ST_X((dp).geom), 'y', ST_Y((dp).geom))) AS vertices
    FROM registro_grafico.gra_cm_mensura_pertenencias_pga07 t
    JOIN LATERAL ST_DumpPoints(t.geom) AS dp ON true
    WHERE t.expte_siged ILIKE $1
    GROUP BY t.expte_siged, t.id_pol, t.sup_reg_ha, t.sup_decla_men_ha, t.mens_id, t.id_pert, t.geom
    ORDER BY t.id_pert::int ASC
";

$result_export = pg_query_params($conn, $sql_export, ['%' . $expediente . '%']);
$export_count = 0;

echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>ID Pert</th><th>Vertices JSON</th><th>Num Vertices</th></tr>";

while ($row = pg_fetch_assoc($result_export)) {
    $export_count++;
    $vertices = json_decode($row['vertices'], true);
    $num_vertices = is_array($vertices) ? count($vertices) : 0;
    
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['id_pert']) . "</td>";
    echo "<td>" . htmlspecialchars(substr($row['vertices'], 0, 100)) . "...</td>";
    echo "<td>" . $num_vertices . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<p><strong>Pertenencias procesadas en consulta de exportación:</strong> $export_count</p>";

pg_close($conn);
?>