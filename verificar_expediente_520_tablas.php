<?php
include 'conectar_bd.php';

$expediente = "520-000942-1997-EXP";

echo "=== VERIFICACIÓN EXPEDIENTE $expediente EN TABLAS ESPECÍFICAS ===\n\n";

// Verificar en gra_cm_manifestaciones_pga07
echo "1. Buscando en gra_cm_manifestaciones_pga07:\n";
$query1 = "SELECT expte_siged, denom, superficie FROM registro_grafico.gra_cm_manifestaciones_pga07 WHERE expte_siged = $1";
$result1 = pg_query_params($db, $query1, [$expediente]);

if ($result1 && pg_num_rows($result1) > 0) {
    echo "   ✓ ENCONTRADO en manifestaciones:\n";
    while ($row = pg_fetch_assoc($result1)) {
        echo "     - Expediente: {$row['expte_siged']}\n";
        echo "     - Denominación: {$row['denom']}\n";
        echo "     - Superficie: {$row['superficie']}\n";
    }
} else {
    echo "   ✗ NO encontrado en manifestaciones\n";
}

echo "\n";

// Verificar en gra_cm_minas_pga07
echo "2. Buscando en gra_cm_minas_pga07:\n";
$query2 = "SELECT expte_siged, denom, superficie FROM registro_grafico.gra_cm_minas_pga07 WHERE expte_siged = $1";
$result2 = pg_query_params($db, $query2, [$expediente]);

if ($result2 && pg_num_rows($result2) > 0) {
    echo "   ✓ ENCONTRADO en minas:\n";
    while ($row = pg_fetch_assoc($result2)) {
        echo "     - Expediente: {$row['expte_siged']}\n";
        echo "     - Denominación: {$row['denom']}\n";
        echo "     - Superficie: {$row['superficie']}\n";
    }
} else {
    echo "   ✗ NO encontrado en minas\n";
}

echo "\n";

// Buscar variaciones del expediente (sin EXP, con otros sufijos)
echo "3. Buscando variaciones del expediente:\n";
$base_expediente = "520-000942-1997";
$variaciones = [
    $base_expediente,
    $base_expediente . "-HIST",
    $base_expediente . "-EXP-CATEO",
    $base_expediente . "-EXP-MANIF"
];

foreach ($variaciones as $variacion) {
    echo "   Buscando: $variacion\n";
    
    // En manifestaciones
    $result_var1 = pg_query_params($db, $query1, [$variacion]);
    if ($result_var1 && pg_num_rows($result_var1) > 0) {
        echo "     ✓ Encontrado en manifestaciones\n";
        while ($row = pg_fetch_assoc($result_var1)) {
            echo "       - Denominación: {$row['denom']}\n";
        }
    }
    
    // En minas
    $result_var2 = pg_query_params($db, $query2, [$variacion]);
    if ($result_var2 && pg_num_rows($result_var2) > 0) {
        echo "     ✓ Encontrado en minas\n";
        while ($row = pg_fetch_assoc($result_var2)) {
            echo "       - Denominación: {$row['denom']}\n";
        }
    }
}

echo "\n";

// Buscar por patrón LIKE en ambas tablas
echo "4. Búsqueda por patrón LIKE '520-000942-1997%':\n";

$query_like1 = "SELECT expte_siged, denom FROM registro_grafico.gra_cm_manifestaciones_pga07 WHERE expte_siged LIKE $1";
$query_like2 = "SELECT expte_siged, denom FROM registro_grafico.gra_cm_minas_pga07 WHERE expte_siged LIKE $1";

$result_like1 = pg_query_params($db, $query_like1, ["520-000942-1997%"]);
$result_like2 = pg_query_params($db, $query_like2, ["520-000942-1997%"]);

echo "   En manifestaciones:\n";
if ($result_like1 && pg_num_rows($result_like1) > 0) {
    while ($row = pg_fetch_assoc($result_like1)) {
        echo "     - {$row['expte_siged']} - {$row['denom']}\n";
    }
} else {
    echo "     Ningún resultado\n";
}

echo "   En minas:\n";
if ($result_like2 && pg_num_rows($result_like2) > 0) {
    while ($row = pg_fetch_assoc($result_like2)) {
        echo "     - {$row['expte_siged']} - {$row['denom']}\n";
    }
} else {
    echo "     Ningún resultado\n";
}

echo "\n=== FIN VERIFICACIÓN ===\n";
?>