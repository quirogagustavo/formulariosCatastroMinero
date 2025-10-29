<?php
include 'conectar_bd.php';

if (!$conn) {
    die("Error de conexión a la base de datos.");
}

$expediente = '1124-000253-2013-EXP';
$params = array('%' . $expediente . '%');

echo "<h1>PRUEBA DE CONSULTA ACTUAL DE EXPORTACIÓN</h1>";
echo "<p>Expediente: $expediente</p>";

// Consulta actual del archivo de exportación
$sql2 = "
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

$result2 = pg_query_params($conn, $sql2, $params);

if ($result2) {
    $count = 0;
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID Pert</th><th>ID Pol</th><th>Superficie</th><th>Vértices</th><th>Estado</th></tr>";
    
    while ($row2 = pg_fetch_assoc($result2)) {
        $count++;
        $vertices = json_decode($row2['vertices'], true);
        $num_vertices = is_array($vertices) ? count($vertices) : 0;
        
        $estado = "OK";
        if ($num_vertices == 1 && isset($vertices[0]['error'])) {
            $estado = "Geometría inválida";
        } elseif ($num_vertices == 0) {
            $estado = "Sin vértices";
        }
        
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row2['id_pert']) . "</td>";
        echo "<td>" . htmlspecialchars($row2['id_pol']) . "</td>";
        echo "<td>" . htmlspecialchars($row2['sup_reg_ha']) . "</td>";
        echo "<td>" . $num_vertices . "</td>";
        echo "<td style='color: " . ($estado == "OK" ? "green" : "orange") . ";'>" . $estado . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    echo "<br><p><strong>TOTAL DE PERTENENCIAS PROCESADAS: $count</strong></p>";
    
    if ($count == 23) {
        echo "<p style='color: green; font-size: 18px; font-weight: bold;'>¡ÉXITO! Se están procesando las 23 pertenencias</p>";
    } elseif ($count == 22) {
        echo "<p style='color: red; font-size: 18px; font-weight: bold;'>PROBLEMA PERSISTE: Solo se procesan 22 pertenencias</p>";
    } else {
        echo "<p style='color: orange; font-size: 18px; font-weight: bold;'>Se procesan $count pertenencias (inesperado)</p>";
    }
    
    pg_free_result($result2);
} else {
    echo "Error en consulta: " . pg_last_error($conn);
}

// Ahora comparemos con una consulta básica
echo "<hr>";
echo "<h2>COMPARACIÓN CON CONSULTA BÁSICA:</h2>";

$sql_basica = "SELECT id_pert, id_pol, sup_reg_ha FROM registro_grafico.gra_cm_mensura_pertenencias_pga07 WHERE expte_siged ILIKE $1 ORDER BY id_pert::int ASC";
$result_basica = pg_query_params($conn, $sql_basica, $params);

$count_basica = 0;
echo "<table border='1'><tr><th>ID Pert</th><th>ID Pol</th><th>Superficie</th></tr>";
while ($row = pg_fetch_assoc($result_basica)) {
    $count_basica++;
    echo "<tr><td>" . $row['id_pert'] . "</td><td>" . $row['id_pol'] . "</td><td>" . $row['sup_reg_ha'] . "</td></tr>";
}
echo "</table>";
echo "<p><strong>Total en consulta básica: $count_basica</strong></p>";

if ($count_basica != $count) {
    echo "<p style='color: red; font-size: 16px;'><strong>DIFERENCIA ENCONTRADA: Consulta básica = $count_basica, Consulta de exportación = $count</strong></p>";
}

pg_close($conn);
?>