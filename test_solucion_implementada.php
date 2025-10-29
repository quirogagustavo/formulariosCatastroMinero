<?php
include 'conectar_bd.php';

if (!$conn) {
    die("Error de conexión a la base de datos.");
}

echo "<h1>PRUEBA DE SOLUCIÓN IMPLEMENTADA</h1>";

$busqueda_expte = '1124-000253-2013-EXP';
echo "<p><strong>Expediente original:</strong> $busqueda_expte</p>";

// Aplicar la misma lógica que ahora usa el sistema
$expediente_base = preg_replace('/-(EXP|MANIF|SOLICITUD).*$/i', '', $busqueda_expte);
$params = array($expediente_base . '%');

echo "<p><strong>Expediente base extraído:</strong> $expediente_base</p>";
echo "<p><strong>Patrón de búsqueda:</strong> " . $params[0] . "</p>";

// Probar la nueva consulta de pertenencias
echo "<h2>CONSULTA CORREGIDA DE PERTENENCIAS:</h2>";

$sql2 = "
    SELECT 
        t.expte_siged,
        t.id_pol,
        t.sup_reg_ha,
        t.sup_decla_men_ha,
        t.mens_id,
        t.id_pert,
        t.geom
    FROM registro_grafico.gra_cm_mensura_pertenencias_pga07 t
    WHERE t.expte_siged LIKE $1
    ORDER BY t.id_pert::int ASC
";

$result2 = pg_query_params($conn, $sql2, $params);

if ($result2) {
    $count = 0;
    $expedientes_encontrados = [];
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>#</th><th>Expediente</th><th>ID Pert</th><th>ID Pol</th><th>Superficie</th></tr>";
    
    while ($row2 = pg_fetch_assoc($result2)) {
        $count++;
        $expediente = $row2['expte_siged'];
        
        if (!in_array($expediente, $expedientes_encontrados)) {
            $expedientes_encontrados[] = $expediente;
        }
        
        echo "<tr>";
        echo "<td>$count</td>";
        echo "<td>" . htmlspecialchars($expediente) . "</td>";
        echo "<td>" . htmlspecialchars($row2['id_pert']) . "</td>";
        echo "<td>" . htmlspecialchars($row2['id_pol']) . "</td>";
        echo "<td>" . htmlspecialchars($row2['sup_reg_ha']) . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    echo "<br>";
    
    echo "<div style='background-color: " . ($count >= 23 ? "#ccffcc" : "#ffcccc") . "; padding: 10px; border: 1px solid " . ($count >= 23 ? "#00cc00" : "#cc0000") . ";'>";
    echo "<p><strong>RESULTADOS:</strong></p>";
    echo "<ul>";
    echo "<li>Total de pertenencias encontradas: <strong>$count</strong></li>";
    echo "<li>Expedientes únicos: <strong>" . count($expedientes_encontrados) . "</strong></li>";
    echo "<li>Expedientes encontrados: " . implode(', ', $expedientes_encontrados) . "</li>";
    echo "</ul>";
    
    if ($count >= 23) {
        echo "<p style='color: green; font-weight: bold;'>¡ÉXITO! Se encontraron 23 o más pertenencias</p>";
    } else {
        echo "<p style='color: red; font-weight: bold;'>Aún faltan pertenencias para llegar a 23</p>";
    }
    echo "</div>";
    
    pg_free_result($result2);
} else {
    echo "Error en consulta: " . pg_last_error($conn);
}

pg_close($conn);
?>