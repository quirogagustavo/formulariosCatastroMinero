<?php
include 'conectar_bd.php';

if (!$conn) {
    die("Error de conexión a la base de datos.");
}

$expediente = '1124-000253-2013-EXP';
echo "<h1>ANÁLISIS DEFINITIVO - Expediente: $expediente</h1>";

// 1. Consulta más básica posible
echo "<h2>1. CONTEO DIRECTO EN TABLA BASE:</h2>";
$sql_count = "SELECT COUNT(*) as total FROM registro_grafico.gra_cm_mensura_pertenencias_pga07 WHERE expte_siged = $1";
$result_count = pg_query_params($conn, $sql_count, [$expediente]);
$count_row = pg_fetch_assoc($result_count);
echo "<p><strong>Total exacto en BD:</strong> " . $count_row['total'] . "</p>";

// 2. Listar TODAS las pertenencias que existen
echo "<h2>2. LISTADO COMPLETO DE PERTENENCIAS:</h2>";
$sql_list = "SELECT id_pert, id_pol, sup_reg_ha FROM registro_grafico.gra_cm_mensura_pertenencias_pga07 WHERE expte_siged = $1 ORDER BY id_pert::int ASC";
$result_list = pg_query_params($conn, $sql_list, [$expediente]);

echo "<table border='1'><tr><th>ID Pert</th><th>ID Pol</th><th>Superficie</th></tr>";
$todas_pertenencias = [];
while ($row = pg_fetch_assoc($result_list)) {
    $todas_pertenencias[] = $row['id_pert'];
    echo "<tr><td>" . $row['id_pert'] . "</td><td>" . $row['id_pol'] . "</td><td>" . $row['sup_reg_ha'] . "</td></tr>";
}
echo "</table>";
echo "<p><strong>IDs encontradas: " . implode(', ', $todas_pertenencias) . "</strong></p>";

// 3. Probar consulta EXACTA del reporte
echo "<h2>3. CONSULTA EXACTA DEL REPORTE:</h2>";
$params = array('%' . $expediente . '%');

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
$pertenencias_reporte = [];
echo "<table border='1'><tr><th>ID Pert</th><th>Superficie</th></tr>";
while ($row = pg_fetch_assoc($result_reporte)) {
    $pertenencias_reporte[] = $row['id_pert'];
    echo "<tr><td>" . $row['id_pert'] . "</td><td>" . $row['sup_reg_ha'] . "</td></tr>";
}
echo "</table>";
echo "<p><strong>IDs en reporte: " . implode(', ', $pertenencias_reporte) . "</strong></p>";
echo "<p><strong>Total en reporte: " . count($pertenencias_reporte) . "</strong></p>";

// 4. Encontrar la pertenencia faltante
$faltantes = array_diff($todas_pertenencias, $pertenencias_reporte);
if (!empty($faltantes)) {
    echo "<h2 style='color: red;'>4. PERTENENCIA(S) FALTANTE(S):</h2>";
    foreach ($faltantes as $faltante) {
        echo "<p style='color: red;'><strong>FALTANTE: Pertenencia ID $faltante</strong></p>";
        
        // Analizar por qué falta esta pertenencia
        $sql_analisis = "SELECT id_pert, ST_IsValid(geom) as geom_valida, ST_NumPoints(geom) as num_puntos, 
                         ST_GeometryType(geom) as tipo_geom, ST_AsText(geom) as geom_text
                         FROM registro_grafico.gra_cm_mensura_pertenencias_pga07 
                         WHERE expte_siged = $1 AND id_pert = $2";
        
        $result_analisis = pg_query_params($conn, $sql_analisis, [$expediente, $faltante]);
        $analisis = pg_fetch_assoc($result_analisis);
        
        echo "<table border='1'>";
        echo "<tr><th>Propiedad</th><th>Valor</th></tr>";
        echo "<tr><td>ID Pertenencia</td><td>" . $analisis['id_pert'] . "</td></tr>";
        echo "<tr><td>Geometría Válida</td><td>" . ($analisis['geom_valida'] == 't' ? 'SÍ' : 'NO') . "</td></tr>";
        echo "<tr><td>Número de Puntos</td><td>" . $analisis['num_puntos'] . "</td></tr>";
        echo "<tr><td>Tipo de Geometría</td><td>" . $analisis['tipo_geom'] . "</td></tr>";
        echo "<tr><td>Geometría (texto)</td><td>" . substr($analisis['geom_text'], 0, 200) . "...</td></tr>";
        echo "</table>";
        
        // Probar ST_DumpPoints específicamente en esta pertenencia
        echo "<h3>Prueba ST_DumpPoints para pertenencia $faltante:</h3>";
        $sql_dump_test = "SELECT ST_X((dp).geom) as x, ST_Y((dp).geom) as y 
                          FROM registro_grafico.gra_cm_mensura_pertenencias_pga07 t
                          JOIN LATERAL ST_DumpPoints(t.geom) AS dp ON true
                          WHERE t.expte_siged = $1 AND t.id_pert = $2";
        
        $result_dump_test = pg_query_params($conn, $sql_dump_test, [$expediente, $faltante]);
        
        if (pg_num_rows($result_dump_test) == 0) {
            echo "<p style='color: red;'><strong>ERROR: ST_DumpPoints no devuelve puntos para esta pertenencia</strong></p>";
        } else {
            echo "<p style='color: green;'>ST_DumpPoints funciona correctamente</p>";
            while ($punto = pg_fetch_assoc($result_dump_test)) {
                echo "Punto: (" . $punto['x'] . ", " . $punto['y'] . ")<br>";
            }
        }
    }
} else {
    echo "<h2 style='color: green;'>4. TODAS LAS PERTENENCIAS ESTÁN PRESENTES EN EL REPORTE</h2>";
}

pg_close($conn);
?>