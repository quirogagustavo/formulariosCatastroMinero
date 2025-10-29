<?php
// Test para verificar el expediente 520-000942-1997
include 'conectar_bd.php';

echo "<h2>🔍 Verificación del Expediente 520-000942-1997</h2>";

// Datos del expediente
$reparticion = "520";
$num_exp = "000942";
$ano = "1997";

// Los sufijos que prueba el sistema
$sufijos = ['EXP', 'EXP-CATEO', 'HIST', 'EXP-MANIF'];

echo "<h3>📋 Expedientes a verificar:</h3>";
foreach ($sufijos as $sufijo) {
    $nroexpediente = "{$reparticion}-{$num_exp}-{$ano}-{$sufijo}";
    echo "<li><strong>{$nroexpediente}</strong></li>";
}

echo "<br><h3>🔍 Búsqueda en Base de Datos Local:</h3>";

if (!$db) {
    echo "<div style='color: red;'>❌ Error: No se pudo conectar a la base de datos</div>";
    exit;
}

foreach ($sufijos as $sufijo) {
    $nroexpediente = "{$reparticion}-{$num_exp}-{$ano}-{$sufijo}";
    
    echo "<h4>Verificando: <code>{$nroexpediente}</code></h4>";
    
    // 1. Verificar en manifestaciones
    $query_manif = "SELECT expte_siged, denom, estado FROM registro_grafico.gra_cm_manifestaciones_pga07 WHERE expte_siged = $1";
    $result_manif = pg_query_params($db, $query_manif, [$nroexpediente]);
    
    if ($result_manif && pg_num_rows($result_manif) > 0) {
        echo "<div style='color: green;'>✅ <strong>ENCONTRADO en gra_cm_manifestaciones_pga07</strong></div>";
        while ($row = pg_fetch_assoc($result_manif)) {
            echo "<ul>";
            echo "<li><strong>Expediente:</strong> {$row['expte_siged']}</li>";
            echo "<li><strong>Denominación:</strong> {$row['denom']}</li>";
            echo "<li><strong>Estado:</strong> {$row['estado']}</li>";
            echo "</ul>";
        }
    } else {
        echo "<div style='color: orange;'>⚠️ No encontrado en gra_cm_manifestaciones_pga07</div>";
    }
    
    // 2. Verificar en minas
    $query_minas = "SELECT expte_siged, denom, estado FROM registro_grafico.gra_cm_minas_pga07 WHERE expte_siged = $1";
    $result_minas = pg_query_params($db, $query_minas, [$nroexpediente]);
    
    if ($result_minas && pg_num_rows($result_minas) > 0) {
        echo "<div style='color: green;'>✅ <strong>ENCONTRADO en gra_cm_minas_pga07</strong></div>";
        while ($row = pg_fetch_assoc($result_minas)) {
            echo "<ul>";
            echo "<li><strong>Expediente:</strong> {$row['expte_siged']}</li>";
            echo "<li><strong>Denominación:</strong> {$row['denom']}</li>";
            echo "<li><strong>Estado:</strong> {$row['estado']}</li>";
            echo "</ul>";
        }
    } else {
        echo "<div style='color: orange;'>⚠️ No encontrado en gra_cm_minas_pga07</div>";
    }
    
    echo "<hr>";
}

// 3. Búsqueda más amplia - por si hay variaciones en el formato
echo "<h3>🔍 Búsqueda Amplia (por si hay variaciones en el formato):</h3>";

$busqueda_amplia = "%520%942%1997%";
echo "<h4>Buscando patrones que contengan: <code>520, 942, 1997</code></h4>";

// Búsqueda en manifestaciones con LIKE
$query_like_manif = "SELECT expte_siged, denom, estado FROM registro_grafico.gra_cm_manifestaciones_pga07 WHERE expte_siged LIKE $1";
$result_like_manif = pg_query_params($db, $query_like_manif, [$busqueda_amplia]);

if ($result_like_manif && pg_num_rows($result_like_manif) > 0) {
    echo "<div style='color: green;'>✅ <strong>Expedientes similares encontrados en gra_cm_manifestaciones_pga07:</strong></div>";
    while ($row = pg_fetch_assoc($result_like_manif)) {
        echo "<ul>";
        echo "<li><strong>Expediente:</strong> {$row['expte_siged']}</li>";
        echo "<li><strong>Denominación:</strong> {$row['denom']}</li>";
        echo "<li><strong>Estado:</strong> {$row['estado']}</li>";
        echo "</ul>";
    }
} else {
    echo "<div style='color: red;'>❌ No se encontraron expedientes similares en gra_cm_manifestaciones_pga07</div>";
}

// Búsqueda en minas con LIKE
$query_like_minas = "SELECT expte_siged, denom, estado FROM registro_grafico.gra_cm_minas_pga07 WHERE expte_siged LIKE $1";
$result_like_minas = pg_query_params($db, $query_like_minas, [$busqueda_amplia]);

if ($result_like_minas && pg_num_rows($result_like_minas) > 0) {
    echo "<div style='color: green;'>✅ <strong>Expedientes similares encontrados en gra_cm_minas_pga07:</strong></div>";
    while ($row = pg_fetch_assoc($result_like_minas)) {
        echo "<ul>";
        echo "<li><strong>Expediente:</strong> {$row['expte_siged']}</li>";
        echo "<li><strong>Denominación:</strong> {$row['denom']}</li>";
        echo "<li><strong>Estado:</strong> {$row['estado']}</li>";
        echo "</ul>";
    }
} else {
    echo "<div style='color: red;'>❌ No se encontraron expedientes similares en gra_cm_minas_pga07</div>";
}

// 4. Verificar cuántos expedientes hay con repartición 520
echo "<h3>📊 Estadísticas de la repartición 520:</h3>";

$query_stats_manif = "SELECT COUNT(*) as total FROM registro_grafico.gra_cm_manifestaciones_pga07 WHERE expte_siged LIKE '520-%'";
$result_stats_manif = pg_query($db, $query_stats_manif);
$stats_manif = pg_fetch_assoc($result_stats_manif);

$query_stats_minas = "SELECT COUNT(*) as total FROM registro_grafico.gra_cm_minas_pga07 WHERE expte_siged LIKE '520-%'";
$result_stats_minas = pg_query($db, $query_stats_minas);
$stats_minas = pg_fetch_assoc($result_stats_minas);

echo "<ul>";
echo "<li><strong>Manifestaciones con repartición 520:</strong> {$stats_manif['total']}</li>";
echo "<li><strong>Minas con repartición 520:</strong> {$stats_minas['total']}</li>";
echo "</ul>";

pg_close($db);
?>