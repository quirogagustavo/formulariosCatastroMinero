<?php
// Simulación exacta de la búsqueda del expediente 520-000942-1997
include 'conectar_bd.php';

echo "<h2>🔍 Simulación Exacta de buscar_expediente_get2.php</h2>";
echo "<h3>Expediente: 520-000942-1997</h3>";

// Datos de entrada (como los ingresaría el usuario)
$num_exp_raw = "942";        // El usuario ingresa sin ceros
$reparticion = "520";
$ano = "1997";

// Formateo como lo hace el sistema
$num_exp = str_pad($num_exp_raw, 6, "0", STR_PAD_LEFT);

echo "<p><strong>Datos de entrada:</strong></p>";
echo "<ul>";
echo "<li>Repartición: {$reparticion}</li>";
echo "<li>Número (ingresado): {$num_exp_raw}</li>";
echo "<li>Número (formateado): {$num_exp}</li>";
echo "<li>Año: {$ano}</li>";
echo "</ul>";

if (!$db) {
    echo "<div style='color: red;'>❌ Error: No se pudo conectar a la base de datos</div>";
    exit;
}

// Función exacta del sistema
function expediente_existe_en_bd($db, $expte_siged) {
    $query = "
        SELECT 1 FROM registro_grafico.gra_cm_manifestaciones_pga07 WHERE expte_siged = $1
        UNION
        SELECT 1 FROM registro_grafico.gra_cm_minas_pga07 WHERE expte_siged = $1
        LIMIT 1
    ";
    $res = pg_query_params($db, $query, [$expte_siged]);
    return pg_num_rows($res) > 0;
}

// Función para obtener denominación
function obtener_denominacion($db, $nroexpediente) {
    $query = "SELECT denom AS denom
                FROM registro_grafico.gra_cm_manifestaciones_pga07
                WHERE expte_siged = $1
                UNION
                SELECT denom AS denom
                FROM registro_grafico.gra_cm_minas_pga07
                WHERE expte_siged = $1
                LIMIT 1;";

    $result = pg_query_params($db, $query, [$nroexpediente]);

    if ($result && pg_num_rows($result) > 0) {
        $row = pg_fetch_assoc($result);
        return $row['denom'];  
    }
    return '';
}

// Simulación del servicio web (simplificada)
function simular_servicio_web($nroexpediente) {
    // Simulo que el servicio responde positivamente para este expediente
    // En la realidad, aquí haría la llamada CURL
    echo "<div style='background: #e7f3ff; padding: 10px; margin: 10px 0;'>";
    echo "📡 <strong>Simulando llamada al servicio web para:</strong> {$nroexpediente}<br>";
    echo "🟢 Respuesta simulada: EXPEDIENTE ENCONTRADO en SIGED";
    echo "</div>";
    
    return [
        'expediente' => [
            'iniciador' => 'SIMULADO - Iniciador del expediente',
            'extracto' => 'SIMULADO - Extracto del expediente'
        ]
    ];
}

echo "<h3>🔄 Proceso de Búsqueda (como en buscar_expediente_get2.php):</h3>";

// Intentos con diferentes sufijos (como en el código original)
foreach (['EXP', 'EXP-CATEO', 'HIST', 'EXP-MANIF'] as $sufijo) {
    $nroexpediente = "{$reparticion}-{$num_exp}-{$ano}-{$sufijo}";
    
    echo "<h4>🔍 Probando: <code>{$nroexpediente}</code></h4>";
    
    // 1. Simular consulta al servicio externo
    $data = simular_servicio_web($nroexpediente);
    
    // 2. Si el servicio responde, verificar en BD local
    if (!empty($data['expediente'])) {
        echo "<div style='color: green;'>✅ Expediente encontrado en servicio web</div>";
        
        // 3. Obtener denominación
        $denom = obtener_denominacion($db, $nroexpediente);
        echo "<div>📝 <strong>Denominación obtenida:</strong> " . ($denom ?: 'No encontrada') . "</div>";
        
        // 4. Verificar existencia en BD local
        if (expediente_existe_en_bd($db, $nroexpediente)) {
            echo "<div style='color: green; font-weight: bold;'>✅ <strong>EXPEDIENTE EXISTE EN BD LOCAL</strong></div>";
            echo "<div style='background: #d4edda; padding: 10px; margin: 10px 0;'>";
            echo "🎉 <strong>RESULTADO FINAL:</strong> El expediente sería aceptado por el sistema<br>";
            echo "📋 <strong>Datos que se completarían:</strong><br>";
            echo "- Iniciador: {$data['expediente']['iniciador']}<br>";
            echo "- Extracto: {$data['expediente']['extracto']}<br>";
            echo "- Denominación: {$denom}<br>";
            echo "- Expediente usado: {$nroexpediente}";
            echo "</div>";
            break; // Salir del bucle como hace el código original
        } else {
            echo "<div style='color: red;'>❌ <strong>EXPEDIENTE NO EXISTE EN BD LOCAL</strong></div>";
            echo "<div style='background: #f8d7da; padding: 10px; margin: 10px 0;'>";
            echo "⚠️ Aunque el expediente existe en SIGED, no está en la base de datos local";
            echo "</div>";
        }
    } else {
        echo "<div style='color: orange;'>⚠️ Expediente no encontrado en servicio web</div>";
    }
    
    echo "<hr>";
}

echo "<h3>🎯 Conclusión:</h3>";
echo "<div style='background: #fff3cd; padding: 15px; margin: 10px 0;'>";
echo "<strong>Para que el expediente 520-000942-1997 funcione en el formulario de mensura:</strong><br>";
echo "1. ✅ Debe existir en el servicio SIGED (esto lo verificarías manualmente)<br>";
echo "2. ❓ Debe existir en gra_cm_manifestaciones_pga07 O gra_cm_minas_pga07<br>";
echo "3. 📝 Si no está en la BD local, necesitas investigar por qué no se sincronizó";
echo "</div>";

pg_close($db);
?>