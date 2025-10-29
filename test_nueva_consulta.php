<?php
include 'conectar_bd.php';

if (!$conn) {
    die("Error de conexión a la base de datos.");
}

$expediente = '1124-000253-2013-EXP';
$params = array('%' . $expediente . '%');

echo "<h2>PRUEBA DE NUEVA CONSULTA CORREGIDA</h2>";
echo "<p>Expediente: $expediente</p>";

// Nueva consulta corregida
$sql2 = "
    SELECT 
        t.expte_siged,
        t.id_pol,
        t.sup_reg_ha,
        t.sup_decla_men_ha,
        t.mens_id,
        t.id_pert,
        t.geom AS geom_original,
        CASE 
            WHEN ST_IsValid(t.geom) AND ST_NumPoints(t.geom) > 0 THEN
                json_agg(json_build_object('x', ST_X((dp).geom), 'y', ST_Y((dp).geom)))
            ELSE 
                '[{\"x\":0,\"y\":0}]'::json
        END AS vertices
    FROM registro_grafico.gra_cm_mensura_pertenencias_pga07 t
    LEFT JOIN LATERAL ST_DumpPoints(t.geom) AS dp ON ST_IsValid(t.geom) AND ST_NumPoints(t.geom) > 0
    WHERE t.expte_siged ILIKE $1
    GROUP BY t.expte_siged, t.id_pol, t.sup_reg_ha, t.sup_decla_men_ha, t.mens_id, t.id_pert, t.geom
    ORDER BY t.id_pert::int ASC
";

$result2 = pg_query_params($conn, $sql2, $params);

if ($result2) {
    $count = 0;
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID Pert</th><th>Superficie</th><th>Vértices</th><th>Estado</th></tr>";
    
    while ($row2 = pg_fetch_assoc($result2)) {
        $count++;
        $vertices = json_decode($row2['vertices'], true);
        $num_vertices = is_array($vertices) ? count($vertices) : 0;
        
        $estado = "OK";
        if ($num_vertices == 1 && $vertices[0]['x'] == 0 && $vertices[0]['y'] == 0) {
            $estado = "Geometría inválida";
        } elseif ($num_vertices == 0) {
            $estado = "Sin vértices";
        }
        
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row2['id_pert']) . "</td>";
        echo "<td>" . htmlspecialchars($row2['sup_reg_ha']) . "</td>";
        echo "<td>" . $num_vertices . "</td>";
        echo "<td style='color: " . ($estado == "OK" ? "green" : "orange") . ";'>" . $estado . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    echo "<br><p><strong>TOTAL DE PERTENENCIAS PROCESADAS: $count</strong></p>";
    
    if ($count == 23) {
        echo "<p style='color: green; font-size: 18px;'><strong>¡ÉXITO! Se están procesando las 23 pertenencias</strong></p>";
    } else {
        echo "<p style='color: red; font-size: 18px;'><strong>AÚN HAY PROBLEMA: Solo se procesan $count pertenencias</strong></p>";
    }
    
    pg_free_result($result2);
} else {
    echo "Error en consulta: " . pg_last_error($conn);
}

pg_close($conn);
?>