<?php
include 'conectar_bd.php';

if (!$conn) {
    die("Error de conexión a la base de datos.");
}

$busqueda_expte = $_GET['expte'] ?? '1124-000253-2013-EXP';

echo "<h1>DEBUG: ¿Qué encuentra la exportación?</h1>";
echo "<p>Parámetro de búsqueda: <strong>$busqueda_expte</strong></p>";

$params = array('%' . $busqueda_expte . '%');

// Simular exactamente lo que hace la exportación
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
    WHERE t.expte_siged ILIKE $1
    ORDER BY t.id_pert::int ASC
";

$result2 = pg_query_params($conn, $sql2, $params);

if ($result2) {
    echo "<h2>RESULTADOS DE LA CONSULTA DE EXPORTACIÓN:</h2>";
    
    $expedientes_encontrados = [];
    $count = 0;
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>#</th><th>Expediente Encontrado</th><th>ID Pert</th><th>ID Pol</th><th>Superficie</th></tr>";
    
    while ($row2 = pg_fetch_assoc($result2)) {
        $count++;
        $expediente_encontrado = $row2['expte_siged'];
        
        if (!in_array($expediente_encontrado, $expedientes_encontrados)) {
            $expedientes_encontrados[] = $expediente_encontrado;
        }
        
        $color = ($expediente_encontrado == $busqueda_expte) ? "#ccffcc" : "#ffcccc";
        
        echo "<tr style='background-color: $color;'>";
        echo "<td>$count</td>";
        echo "<td>" . htmlspecialchars($expediente_encontrado) . "</td>";
        echo "<td>" . htmlspecialchars($row2['id_pert']) . "</td>";
        echo "<td>" . htmlspecialchars($row2['id_pol']) . "</td>";
        echo "<td>" . htmlspecialchars($row2['sup_reg_ha']) . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    echo "<br>";
    echo "<p><strong>Total de pertenencias encontradas: $count</strong></p>";
    echo "<p><strong>Expedientes únicos encontrados: " . count($expedientes_encontrados) . "</strong></p>";
    
    if (count($expedientes_encontrados) > 1) {
        echo "<div style='background-color: #ffcccc; padding: 10px; border: 2px solid #cc0000;'>";
        echo "<h3>¡PROBLEMA IDENTIFICADO!</h3>";
        echo "<p>La búsqueda está encontrando múltiples expedientes:</p>";
        echo "<ul>";
        foreach ($expedientes_encontrados as $exp) {
            echo "<li><strong>" . htmlspecialchars($exp) . "</strong></li>";
        }
        echo "</ul>";
        echo "<p>Esto explica por qué aparecen menos pertenencias de las esperadas.</p>";
        echo "</div>";
        
        // Mostrar conteo por expediente
        echo "<h3>CONTEO POR EXPEDIENTE:</h3>";
        echo "<table border='1'>";
        echo "<tr><th>Expediente</th><th>Pertenencias</th></tr>";
        
        foreach ($expedientes_encontrados as $exp) {
            $sql_count = "SELECT COUNT(*) as total FROM registro_grafico.gra_cm_mensura_pertenencias_pga07 WHERE expte_siged = $1";
            $result_count = pg_query_params($conn, $sql_count, [$exp]);
            $count_row = pg_fetch_assoc($result_count);
            
            echo "<tr>";
            echo "<td>" . htmlspecialchars($exp) . "</td>";
            echo "<td>" . $count_row['total'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    pg_free_result($result2);
} else {
    echo "Error en consulta: " . pg_last_error($conn);
}

pg_close($conn);
?>