<?php
include 'conectar_bd.php';

if (!$conn) {
    die("Error de conexión a la base de datos.");
}

$expediente = '1124-000253-2013-EXP';
echo "<h1>ANÁLISIS DEFINITIVO - Expediente: $expediente</h1>";

// 1. Contar pertenencias base
echo "<h2>1. CONTEO BASE EN LA TABLA</h2>";
$sql_count = "SELECT COUNT(*) as total FROM registro_grafico.gra_cm_mensura_pertenencias_pga07 WHERE expte_siged ILIKE $1";
$result_count = pg_query_params($conn, $sql_count, ['%' . $expediente . '%']);
$count_row = pg_fetch_assoc($result_count);
echo "<p><strong>TOTAL EN LA TABLA: " . $count_row['total'] . "</strong></p>";

// 2. Listar TODAS las pertenencias con detalles
echo "<h2>2. LISTADO COMPLETO DE PERTENENCIAS</h2>";
$sql_list = "SELECT id_pert, id_pol, sup_reg_ha, 
                    ST_IsValid(geom) as valida, 
                    ST_IsEmpty(geom) as vacia,
                    ST_NumPoints(geom) as puntos,
                    ST_GeometryType(geom) as tipo
             FROM registro_grafico.gra_cm_mensura_pertenencias_pga07 
             WHERE expte_siged ILIKE $1 
             ORDER BY id_pert::int ASC";
$result_list = pg_query_params($conn, $sql_list, ['%' . $expediente . '%']);

echo "<table border='1' style='border-collapse: collapse; width:100%;'>";
echo "<tr style='background-color: #f0f0f0;'>";
echo "<th>ID Pert</th><th>ID Pol</th><th>Superficie</th><th>Válida</th><th>Vacía</th><th>Puntos</th><th>Tipo</th>";
echo "</tr>";

$pertenencias_encontradas = [];
while ($row = pg_fetch_assoc($result_list)) {
    $pertenencias_encontradas[] = $row['id_pert'];
    $color = "white";
    if ($row['valida'] == 'f' || $row['vacia'] == 't' || $row['puntos'] < 3) {
        $color = "#ffcccc"; // Rojo claro para problemáticas
    }
    
    echo "<tr style='background-color: $color;'>";
    echo "<td>" . $row['id_pert'] . "</td>";
    echo "<td>" . $row['id_pol'] . "</td>";
    echo "<td>" . $row['sup_reg_ha'] . "</td>";
    echo "<td>" . ($row['valida'] == 't' ? 'Sí' : 'No') . "</td>";
    echo "<td>" . ($row['vacia'] == 't' ? 'Sí' : 'No') . "</td>";
    echo "<td>" . $row['puntos'] . "</td>";
    echo "<td>" . $row['tipo'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// 3. Simular la consulta de REPORTE
echo "<h2>3. SIMULACIÓN CONSULTA REPORTE</h2>";
$sql_reporte = "
    SELECT 
        t.id_pert,
        t.sup_reg_ha
    FROM registro_grafico.gra_cm_mensura_pertenencias_pga07 t
    JOIN LATERAL ST_DumpPoints(t.geom) AS dp ON true
    WHERE t.expte_siged ILIKE $1
    GROUP BY t.expte_siged, t.id_pol, t.sup_reg_ha, t.sup_decla_men_ha, t.mens_id, t.id_pert, t.geom
    ORDER BY t.id_pert::int ASC
";

$result_reporte = pg_query_params($conn, $sql_reporte, ['%' . $expediente . '%']);
$pertenencias_reporte = [];
$count_reporte = 0;

echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>ID Pert</th><th>Superficie</th></tr>";
while ($row = pg_fetch_assoc($result_reporte)) {
    $count_reporte++;
    $pertenencias_reporte[] = $row['id_pert'];
    echo "<tr><td>" . $row['id_pert'] . "</td><td>" . $row['sup_reg_ha'] . "</td></tr>";
}
echo "</table>";
echo "<p><strong>TOTAL EN REPORTE: $count_reporte</strong></p>";

// 4. Simular la consulta de EXPORTACIÓN ACTUAL
echo "<h2>4. SIMULACIÓN CONSULTA EXPORTACIÓN ACTUAL</h2>";
$sql_export = "
    SELECT 
        t.id_pert,
        t.sup_reg_ha
    FROM registro_grafico.gra_cm_mensura_pertenencias_pga07 t
    LEFT JOIN LATERAL ST_DumpPoints(t.geom) AS dp ON ST_IsValid(t.geom) AND ST_NumPoints(t.geom) > 0
    WHERE t.expte_siged ILIKE $1
    GROUP BY t.expte_siged, t.id_pol, t.sup_reg_ha, t.sup_decla_men_ha, t.mens_id, t.id_pert, t.geom
    ORDER BY t.id_pert::int ASC
";

$result_export = pg_query_params($conn, $sql_export, ['%' . $expediente . '%']);
$pertenencias_export = [];
$count_export = 0;

echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>ID Pert</th><th>Superficie</th></tr>";
while ($row = pg_fetch_assoc($result_export)) {
    $count_export++;
    $pertenencias_export[] = $row['id_pert'];
    echo "<tr><td>" . $row['id_pert'] . "</td><td>" . $row['sup_reg_ha'] . "</td></tr>";
}
echo "</table>";
echo "<p><strong>TOTAL EN EXPORTACIÓN: $count_export</strong></p>";

// 5. Encontrar diferencias
echo "<h2>5. ANÁLISIS DE DIFERENCIAS</h2>";

$faltantes_en_reporte = array_diff($pertenencias_encontradas, $pertenencias_reporte);
$faltantes_en_export = array_diff($pertenencias_encontradas, $pertenencias_export);

echo "<h3>Pertenencias que faltan en REPORTE:</h3>";
if (empty($faltantes_en_reporte)) {
    echo "<p style='color: green;'>Ninguna</p>";
} else {
    echo "<p style='color: red;'>Faltan: " . implode(", ", $faltantes_en_reporte) . "</p>";
}

echo "<h3>Pertenencias que faltan en EXPORTACIÓN:</h3>";
if (empty($faltantes_en_export)) {
    echo "<p style='color: green;'>Ninguna</p>";
} else {
    echo "<p style='color: red;'>Faltan: " . implode(", ", $faltantes_en_export) . "</p>";
}

// 6. Consulta sin GROUP BY para verificar
echo "<h2>6. VERIFICACIÓN SIN GROUP BY</h2>";
$sql_simple = "
    SELECT COUNT(DISTINCT t.id_pert) as total
    FROM registro_grafico.gra_cm_mensura_pertenencias_pga07 t
    JOIN LATERAL ST_DumpPoints(t.geom) AS dp ON true
    WHERE t.expte_siged ILIKE $1
";

$result_simple = pg_query_params($conn, $sql_simple, ['%' . $expediente . '%']);
$simple_row = pg_fetch_assoc($result_simple);
echo "<p><strong>Pertenencias distintas con ST_DumpPoints: " . $simple_row['total'] . "</strong></p>";

// 7. Propuesta de consulta DEFINITIVA
echo "<h2>7. CONSULTA PROPUESTA DEFINITIVA</h2>";
$sql_definitiva = "
    SELECT 
        t.expte_siged,
        t.id_pol,
        t.sup_reg_ha,
        t.sup_decla_men_ha,
        t.mens_id,
        t.id_pert,
        CASE 
            WHEN ST_IsValid(t.geom) AND ST_NumPoints(t.geom) > 0 THEN
                (SELECT json_agg(json_build_object('x', ST_X(dp.geom), 'y', ST_Y(dp.geom))) 
                 FROM ST_DumpPoints(t.geom) dp)
            ELSE 
                '[{\"x\":0,\"y\":0}]'::json
        END AS vertices
    FROM registro_grafico.gra_cm_mensura_pertenencias_pga07 t
    WHERE t.expte_siged ILIKE $1
    ORDER BY t.id_pert::int ASC
";

$result_definitiva = pg_query_params($conn, $sql_definitiva, ['%' . $expediente . '%']);
$count_definitiva = 0;

echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>ID Pert</th><th>Superficie</th><th>Vértices OK</th></tr>";
while ($row = pg_fetch_assoc($result_definitiva)) {
    $count_definitiva++;
    $vertices = json_decode($row['vertices'], true);
    $vertices_ok = (is_array($vertices) && count($vertices) > 1) ? "Sí" : "No";
    
    echo "<tr>";
    echo "<td>" . $row['id_pert'] . "</td>";
    echo "<td>" . $row['sup_reg_ha'] . "</td>";
    echo "<td>" . $vertices_ok . "</td>";
    echo "</tr>";
}
echo "</table>";
echo "<p><strong>TOTAL CON CONSULTA DEFINITIVA: $count_definitiva</strong></p>";

if ($count_definitiva == $count_row['total']) {
    echo "<p style='color: green; font-size: 18px;'><strong>¡ESTA CONSULTA FUNCIONA! Incluye todas las pertenencias</strong></p>";
} else {
    echo "<p style='color: red; font-size: 18px;'><strong>Aún hay problemas</strong></p>";
}

pg_close($conn);
?>