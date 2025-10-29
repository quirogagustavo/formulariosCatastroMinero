<?php
include 'conectar_bd.php';

if (!$conn) {
    die("Error de conexión a la base de datos.");
}

echo "<h1>ANÁLISIS: Búsqueda por número base de expediente</h1>";

$expediente_completo = '1124-000253-2013-EXP';
// Extraer solo la parte numérica: 1124-000253-2013
$expediente_base = preg_replace('/-(EXP|MANIF|SOLICITUD).*$/i', '', $expediente_completo);

echo "<p><strong>Expediente completo:</strong> $expediente_completo</p>";
echo "<p><strong>Expediente base extraído:</strong> $expediente_base</p>";

// 1. Buscar con el número base
echo "<h2>1. BÚSQUEDA CON NÚMERO BASE:</h2>";
$params_base = array($expediente_base . '%');

$sql_base = "SELECT expte_siged, COUNT(*) as pertenencias 
             FROM registro_grafico.gra_cm_mensura_pertenencias_pga07 
             WHERE expte_siged LIKE $1
             GROUP BY expte_siged
             ORDER BY expte_siged";

$result_base = pg_query_params($conn, $sql_base, $params_base);

echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>Expediente Encontrado</th><th>Pertenencias</th></tr>";

$total_con_base = 0;
$expedientes_con_base = [];

while ($row = pg_fetch_assoc($result_base)) {
    $expedientes_con_base[] = $row['expte_siged'];
    $total_con_base += $row['pertenencias'];
    
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['expte_siged']) . "</td>";
    echo "<td>" . $row['pertenencias'] . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<p><strong>Total de expedientes encontrados: " . count($expedientes_con_base) . "</strong></p>";
echo "<p><strong>Total de pertenencias: $total_con_base</strong></p>";

// 2. Mostrar todas las pertenencias encontradas
if ($total_con_base > 0) {
    echo "<h2>2. TODAS LAS PERTENENCIAS ENCONTRADAS:</h2>";
    
    $sql_todas = "SELECT expte_siged, id_pert, id_pol, sup_reg_ha 
                  FROM registro_grafico.gra_cm_mensura_pertenencias_pga07 
                  WHERE expte_siged LIKE $1
                  ORDER BY expte_siged, id_pert::int ASC";
    
    $result_todas = pg_query_params($conn, $sql_todas, $params_base);
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Expediente</th><th>ID Pert</th><th>ID Pol</th><th>Superficie</th></tr>";
    
    $current_exp = '';
    while ($row = pg_fetch_assoc($result_todas)) {
        if ($row['expte_siged'] != $current_exp) {
            $current_exp = $row['expte_siged'];
            $bgcolor = ($current_exp == $expedientes_con_base[0]) ? '#ccffcc' : '#ffcccc';
        }
        
        echo "<tr style='background-color: $bgcolor;'>";
        echo "<td>" . htmlspecialchars($row['expte_siged']) . "</td>";
        echo "<td>" . $row['id_pert'] . "</td>";
        echo "<td>" . $row['id_pol'] . "</td>";
        echo "<td>" . $row['sup_reg_ha'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// 3. Propuesta de solución
echo "<h2>3. PROPUESTA DE SOLUCIÓN:</h2>";

if ($total_con_base >= 23) {
    echo "<div style='background-color: #ccffcc; padding: 10px; border: 1px solid #00cc00;'>";
    echo "<p><strong>¡SOLUCIÓN ENCONTRADA!</strong></p>";
    echo "<p>Usando la búsqueda por número base se encuentran <strong>$total_con_base pertenencias</strong> que incluyen las 23 esperadas.</p>";
    echo "<p><strong>Recomendación:</strong> Modificar la lógica de búsqueda en reporte y exportación para usar el número base.</p>";
    echo "</div>";
} else {
    echo "<div style='background-color: #ffffcc; padding: 10px; border: 1px solid #cccc00;'>";
    echo "<p><strong>Problema parcialmente resuelto</strong></p>";
    echo "<p>Se encontraron $total_con_base pertenencias, pero aún faltan para llegar a 23.</p>";
    echo "</div>";
}

pg_close($conn);
?>