<?php
include 'conectar_bd.php';

if (!$conn) {
    die("Error de conexión a la base de datos.");
}

echo "<h1>ANÁLISIS DE BÚSQUEDA CON ILIKE</h1>";

$expediente_busqueda = '1124-000253-2013-EXP';
echo "<p>Buscando con: <strong>$expediente_busqueda</strong></p>";

// 1. Simular la búsqueda exacta como hace el sistema
echo "<h2>1. BÚSQUEDA COMO LA HACE EL SISTEMA (ILIKE '%expediente%'):</h2>";
$params = array('%' . $expediente_busqueda . '%');

$sql_sistema = "SELECT expte_siged, COUNT(*) as pertenencias 
                FROM registro_grafico.gra_cm_mensura_pertenencias_pga07 
                WHERE expte_siged ILIKE $1
                GROUP BY expte_siged
                ORDER BY expte_siged";

$result_sistema = pg_query_params($conn, $sql_sistema, $params);

echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>Expediente Encontrado</th><th>Pertenencias</th><th>¿Coincide?</th></tr>";

$total_pertenencias = 0;
while ($row = pg_fetch_assoc($result_sistema)) {
    $expediente_encontrado = $row['expte_siged'];
    $pertenencias = $row['pertenencias'];
    $total_pertenencias += $pertenencias;
    
    $coincide = ($expediente_encontrado == $expediente_busqueda) ? "EXACTA" : "PARCIAL";
    $color = ($coincide == "EXACTA") ? "green" : "orange";
    
    echo "<tr>";
    echo "<td>" . htmlspecialchars($expediente_encontrado) . "</td>";
    echo "<td>" . $pertenencias . "</td>";
    echo "<td style='color: $color; font-weight: bold;'>" . $coincide . "</td>";
    echo "</tr>";
}
echo "</table>";
echo "<p><strong>Total de pertenencias encontradas con ILIKE: $total_pertenencias</strong></p>";

// 2. Búsqueda exacta
echo "<h2>2. BÚSQUEDA EXACTA (=):</h2>";
$sql_exacta = "SELECT expte_siged, COUNT(*) as pertenencias 
               FROM registro_grafico.gra_cm_mensura_pertenencias_pga07 
               WHERE expte_siged = $1
               GROUP BY expte_siged";

$result_exacta = pg_query_params($conn, $sql_exacta, [$expediente_busqueda]);

echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>Expediente Encontrado</th><th>Pertenencias</th></tr>";

$pertenencias_exactas = 0;
while ($row = pg_fetch_assoc($result_exacta)) {
    $pertenencias_exactas = $row['pertenencias'];
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['expte_siged']) . "</td>";
    echo "<td>" . $pertenencias_exactas . "</td>";
    echo "</tr>";
}

if ($pertenencias_exactas == 0) {
    echo "<tr><td colspan='2' style='color: red;'>No se encontraron coincidencias exactas</td></tr>";
}
echo "</table>";
echo "<p><strong>Total con búsqueda exacta: $pertenencias_exactas</strong></p>";

// 3. Buscar variaciones del expediente
echo "<h2>3. VARIACIONES POSIBLES DEL EXPEDIENTE:</h2>";
$base_expediente = '1124-000253-2013';
$variaciones = [
    $base_expediente,
    $base_expediente . '-EXP',
    $base_expediente . '-MANIF',
    $base_expediente . 'EXP',
    $base_expediente . 'MANIF'
];

echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>Variación Buscada</th><th>Existe</th><th>Pertenencias</th></tr>";

foreach ($variaciones as $variacion) {
    $sql_var = "SELECT COUNT(*) as pertenencias 
                FROM registro_grafico.gra_cm_mensura_pertenencias_pga07 
                WHERE expte_siged = $1";
    
    $result_var = pg_query_params($conn, $sql_var, [$variacion]);
    $row_var = pg_fetch_assoc($result_var);
    $pertenencias_var = $row_var['pertenencias'];
    
    $existe = ($pertenencias_var > 0) ? "SÍ" : "NO";
    $color = ($pertenencias_var > 0) ? "green" : "red";
    
    echo "<tr>";
    echo "<td>" . htmlspecialchars($variacion) . "</td>";
    echo "<td style='color: $color; font-weight: bold;'>" . $existe . "</td>";
    echo "<td>" . $pertenencias_var . "</td>";
    echo "</tr>";
}
echo "</table>";

// 4. Recomendación
echo "<h2>4. DIAGNÓSTICO Y SOLUCIÓN:</h2>";

if ($total_pertenencias != $pertenencias_exactas && $total_pertenencias > 0) {
    echo "<div style='background-color: #ffcccc; padding: 10px; border: 1px solid #cc0000;'>";
    echo "<p><strong>PROBLEMA IDENTIFICADO:</strong></p>";
    echo "<p>La búsqueda con ILIKE encuentra <strong>$total_pertenencias pertenencias</strong> pero probablemente están distribuidas entre múltiples variaciones del expediente.</p>";
    echo "<p><strong>Solución:</strong> Unificar los números de expediente en la base de datos o ajustar la lógica de búsqueda.</p>";
    echo "</div>";
} elseif ($pertenencias_exactas > 0) {
    echo "<div style='background-color: #ccffcc; padding: 10px; border: 1px solid #00cc00;'>";
    echo "<p><strong>EXPEDIENTE ÚNICO ENCONTRADO:</strong></p>";
    echo "<p>Se encontraron <strong>$pertenencias_exactas pertenencias</strong> para el expediente exacto.</p>";
    echo "</div>";
} else {
    echo "<div style='background-color: #ffffcc; padding: 10px; border: 1px solid #cccc00;'>";
    echo "<p><strong>NO SE ENCONTRÓ EL EXPEDIENTE:</strong></p>";
    echo "<p>El expediente <strong>$expediente_busqueda</strong> no existe en la base de datos.</p>";
    echo "</div>";
}

pg_close($conn);
?>