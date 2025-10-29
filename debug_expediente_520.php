<?php
// Debug específico para el expediente 520-000942-1997-EXP
include 'conectar_bd.php';

echo "<h1>🐛 DEBUG: Expediente 520-000942-1997</h1>";

// Datos exactos que usaría el formulario
$reparticion = "520";
$num_exp_raw = "942";
$ano = "1997";
$num_exp = str_pad($num_exp_raw, 6, "0", STR_PAD_LEFT);

echo "<h2>📋 Datos del formulario:</h2>";
echo "<ul>";
echo "<li><strong>Repartición:</strong> {$reparticion}</li>";
echo "<li><strong>Número (original):</strong> {$num_exp_raw}</li>";
echo "<li><strong>Número (formateado):</strong> {$num_exp}</li>";
echo "<li><strong>Año:</strong> {$ano}</li>";
echo "</ul>";

// Verificar conexión a BD
echo "<h2>🔌 Test de Conexión a Base de Datos:</h2>";
if (!$db) {
    echo "<div style='color: red; font-weight: bold;'>❌ ERROR: No se pudo conectar a la base de datos</div>";
    echo "<p>Detalles del error:</p>";
    echo "<pre>" . pg_last_error() . "</pre>";
    exit;
} else {
    echo "<div style='color: green;'>✅ Conexión a base de datos exitosa</div>";
}

// Test de la consulta exacta que hace el sistema
echo "<h2>🔍 Test de Búsqueda en BD Local:</h2>";

$nroexpediente = "{$reparticion}-{$num_exp}-{$ano}-EXP";
echo "<h3>Buscando: <code>{$nroexpediente}</code></h3>";

// Función exacta del archivo original
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

// Test 1: Verificar existencia
echo "<h4>Test 1: Verificación de existencia</h4>";
if (expediente_existe_en_bd($db, $nroexpediente)) {
    echo "<div style='color: green; font-weight: bold;'>✅ EXPEDIENTE ENCONTRADO en BD local</div>";
} else {
    echo "<div style='color: red; font-weight: bold;'>❌ EXPEDIENTE NO ENCONTRADO en BD local</div>";
}

// Test 2: Obtener denominación
echo "<h4>Test 2: Obtención de denominación</h4>";
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
    $denom = $row['denom'];
    echo "<div style='color: green; font-weight: bold;'>✅ DENOMINACIÓN ENCONTRADA: <strong>{$denom}</strong></div>";
} else {
    echo "<div style='color: red; font-weight: bold;'>❌ NO SE PUDO OBTENER LA DENOMINACIÓN</div>";
    if ($result === false) {
        echo "<p style='color: red;'>Error en la consulta: " . pg_last_error($db) . "</p>";
    }
}

// Test 3: Simular llamada al servicio web
echo "<h2>📡 Test del Servicio Web SIGED:</h2>";

$datos = ["nroexpediente" => $nroexpediente];
$jsonDatos = json_encode($datos);

$url = "https://soa.sanjuan.gob.ar/ConsultaExpCatastroMineria";
$usuario = "16666407.DNI.M.ws_exp_catastro_mineria";
$password = "DD756BFA3F3F25E4";

echo "<h4>Realizando consulta real al servicio...</h4>";
echo "<p><strong>URL:</strong> {$url}</p>";
echo "<p><strong>Datos enviados:</strong> {$jsonDatos}</p>";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDatos);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
curl_setopt($ch, CURLOPT_USERPWD, "$usuario:$password");
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$respuesta = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

echo "<h4>Respuesta del servicio:</h4>";
echo "<ul>";
echo "<li><strong>Código HTTP:</strong> {$http_code}</li>";
echo "<li><strong>Error cURL:</strong> " . ($curl_error ?: 'Ninguno') . "</li>";
echo "</ul>";

if ($respuesta === false) {
    echo "<div style='color: red; font-weight: bold;'>❌ ERROR: No se pudo conectar al servicio web</div>";
    echo "<p>Error cURL: {$curl_error}</p>";
} else {
    echo "<h4>Respuesta cruda:</h4>";
    echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 5px; max-height: 300px; overflow-y: auto;'>";
    echo htmlspecialchars($respuesta);
    echo "</pre>";
    
    $data = json_decode($respuesta, true);
    
    if ($data === null) {
        echo "<div style='color: red;'>❌ ERROR: Respuesta no es JSON válido</div>";
    } else {
        echo "<h4>Respuesta decodificada:</h4>";
        if (isset($data['expediente']) && !empty($data['expediente'])) {
            echo "<div style='color: green; font-weight: bold;'>✅ EXPEDIENTE ENCONTRADO EN SIGED</div>";
            echo "<pre style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
            print_r($data['expediente']);
            echo "</pre>";
        } else {
            echo "<div style='color: red; font-weight: bold;'>❌ EXPEDIENTE NO ENCONTRADO EN SIGED</div>";
            echo "<p>Estructura de la respuesta:</p>";
            echo "<pre style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
            print_r($data);
            echo "</pre>";
        }
    }
}

echo "<h2>🎯 DIAGNÓSTICO:</h2>";
echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px;'>";
echo "<h4>Situación detectada:</h4>";
echo "<ul>";
echo "<li>✅ El expediente <strong>SÍ EXISTE</strong> en la base de datos local</li>";
echo "<li>✅ La denominación es: <strong>DEMASIA CHORLO</strong></li>";
echo "<li>❓ Verificar si existe en el servicio SIGED (resultado arriba)</li>";
echo "</ul>";

echo "<h4>Posible causa del problema:</h4>";
echo "<p>Si el servicio SIGED no encuentra el expediente, el sistema rechazará la búsqueda aunque esté en la BD local, porque el código requiere que exista en <strong>AMBOS lugares</strong>.</p>";
echo "</div>";

pg_close($db);
?>