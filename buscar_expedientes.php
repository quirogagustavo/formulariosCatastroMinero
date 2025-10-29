<?php
include 'conectar_bd.php';

if (!$conn) {
    die("Error de conexión a la base de datos.");
}

echo "<h1>BÚSQUEDA DE EXPEDIENTES SIMILARES</h1>";

// Buscar todos los expedientes que contengan 253 y 2013
$sql_buscar = "SELECT DISTINCT expte_siged FROM registro_grafico.gra_cm_mensura_pertenencias_pga07 
               WHERE expte_siged ILIKE '%253%' AND expte_siged ILIKE '%2013%'
               ORDER BY expte_siged";

$result_buscar = pg_query($conn, $sql_buscar);

echo "<h2>Expedientes encontrados con '253' y '2013':</h2>";
echo "<table border='1'><tr><th>Expediente</th><th>Pertenencias</th></tr>";

while ($row = pg_fetch_assoc($result_buscar)) {
    $expediente = $row['expte_siged'];
    
    // Contar pertenencias para cada expediente
    $sql_count = "SELECT COUNT(*) as total FROM registro_grafico.gra_cm_mensura_pertenencias_pga07 WHERE expte_siged = $1";
    $result_count = pg_query_params($conn, $sql_count, [$expediente]);
    $count_row = pg_fetch_assoc($result_count);
    
    echo "<tr>";
    echo "<td><strong>" . htmlspecialchars($expediente) . "</strong></td>";
    echo "<td>" . $count_row['total'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// Buscar expedientes con exactamente 23 pertenencias
echo "<h2>Expedientes con 23 pertenencias:</h2>";
$sql_23 = "SELECT expte_siged, COUNT(*) as total 
           FROM registro_grafico.gra_cm_mensura_pertenencias_pga07 
           GROUP BY expte_siged 
           HAVING COUNT(*) = 23 
           ORDER BY expte_siged";

$result_23 = pg_query($conn, $sql_23);

echo "<table border='1'><tr><th>Expediente</th><th>Pertenencias</th></tr>";
while ($row = pg_fetch_assoc($result_23)) {
    echo "<tr>";
    echo "<td><strong>" . htmlspecialchars($row['expte_siged']) . "</strong></td>";
    echo "<td>" . $row['total'] . "</td>";
    echo "</tr>";
}
echo "</table>";

pg_close($conn);
?>