<?php
// Test directo del endpoint que usa el formulario
echo "<h1>🧪 Test Directo del Endpoint de Búsqueda</h1>";
echo "<h2>Simulando exactamente lo que hace el formulario</h2>";

// Parámetros exactos que enviaría el formulario
$params = [
    'reparticion' => '520',
    'num_exp' => '942',
    'ano' => '1997'
];

$url = 'http://localhost:8000/buscar_expediente_get2.php?' . http_build_query($params);

echo "<h3>📡 Llamada que hace el formulario:</h3>";
echo "<p><strong>URL:</strong> <code>{$url}</code></p>";

echo "<h3>📥 Respuesta del endpoint:</h3>";

// Realizar la misma llamada que hace el JavaScript
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$respuesta = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

echo "<ul>";
echo "<li><strong>Código HTTP:</strong> {$http_code}</li>";
echo "<li><strong>Error cURL:</strong> " . ($curl_error ?: 'Ninguno') . "</li>";
echo "</ul>";

if ($respuesta === false) {
    echo "<div style='color: red; font-weight: bold;'>❌ ERROR: No se pudo conectar al endpoint</div>";
} else {
    echo "<h4>Respuesta cruda:</h4>";
    echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 5px; max-height: 400px; overflow-y: auto;'>";
    echo htmlspecialchars($respuesta);
    echo "</pre>";
    
    $data = json_decode($respuesta, true);
    
    if ($data === null) {
        echo "<div style='color: orange;'>⚠️ La respuesta no es JSON válido (puede ser HTML de error)</div>";
    } else {
        echo "<h4>Respuesta JSON decodificada:</h4>";
        if (isset($data['expediente'])) {
            echo "<div style='color: green; font-weight: bold;'>✅ EXPEDIENTE ENCONTRADO</div>";
            echo "<pre style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
            print_r($data);
            echo "</pre>";
        } else if (isset($data['error'])) {
            echo "<div style='color: red; font-weight: bold;'>❌ ERROR: {$data['error']}</div>";
        } else {
            echo "<div style='color: orange;'>❓ Respuesta inesperada:</div>";
            echo "<pre style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
            print_r($data);
            echo "</pre>";
        }
    }
}

echo "<h3>🎯 Interpretación:</h3>";
echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 5px;'>";
echo "<p>Este test muestra <strong>exactamente</strong> lo que ve el formulario cuando busca el expediente.</p>";
echo "<ul>";
echo "<li>Si muestra un error, ahí está el problema</li>";
echo "<li>Si muestra datos del expediente, entonces el formulario debería funcionar</li>";
echo "<li>Código 404 = Expediente no encontrado</li>";
echo "<li>Código 500 = Error del servidor</li>";
echo "<li>Código 200 = Búsqueda exitosa</li>";
echo "</ul>";
echo "</div>";
?>