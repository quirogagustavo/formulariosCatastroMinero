<?php
echo "<h1>PRUEBA DE BÚSQUEDA DE EXPEDIENTE: 520-000942-1997</h1>";

// Simular la búsqueda como lo hace el sistema
$reparticion = '520';
$num_exp_raw = '942';
$ano = '1997';

// Formatear el número como lo hace el sistema
$num_exp = str_pad($num_exp_raw, 6, "0", STR_PAD_LEFT);

echo "<p><strong>Parámetros de entrada:</strong></p>";
echo "<ul>";
echo "<li>Repartición: $reparticion</li>";
echo "<li>Número expediente (original): $num_exp_raw</li>";
echo "<li>Número expediente (formateado): $num_exp</li>";
echo "<li>Año: $ano</li>";
echo "</ul>";

function consultar_expediente($reparticion, $num_exp, $ano, $sufijo) {
    $nroexpediente = "{$reparticion}-{$num_exp}-{$ano}-{$sufijo}";
    $datos = ["nroexpediente" => $nroexpediente];
    $jsonDatos = json_encode($datos);

    echo "<h3>Probando: $nroexpediente</h3>";

    $url = "https://soa.sanjuan.gob.ar/ConsultaExpCatastroMineria";
    $usuario = "16666407.DNI.M.ws_exp_catastro_mineria";
    $password = "DD756BFA3F3F25E4";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDatos);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    curl_setopt($ch, CURLOPT_USERPWD, "$usuario:$password");
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $respuesta = curl_exec($ch);
    $error = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "<p><strong>Código HTTP:</strong> $httpCode</p>";
    
    if ($error) {
        echo "<p style='color: red;'><strong>Error cURL:</strong> $error</p>";
        return null;
    }

    $data = json_decode($respuesta, true);
    
    echo "<p><strong>Respuesta JSON:</strong></p>";
    echo "<pre>" . htmlspecialchars(json_encode($data, JSON_PRETTY_PRINT)) . "</pre>";

    if (!empty($data["expediente"])) {
        echo "<p style='color: green; font-weight: bold;'>✅ EXPEDIENTE ENCONTRADO</p>";
        return [
            "data" => $data,
            "nroexpediente" => $nroexpediente
        ];
    } else {
        echo "<p style='color: red;'>❌ Expediente no encontrado</p>";
        return null;
    }
}

// Probar todas las variaciones como lo hace el sistema
$sufijos = ["EXP", "EXP-CATEO", "HIST", "EXP-MANIF"];

echo "<h2>PROBANDO TODAS LAS VARIACIONES:</h2>";

$encontrado = false;

foreach ($sufijos as $sufijo) {
    $resultado = consultar_expediente($reparticion, $num_exp, $ano, $sufijo);
    
    if ($resultado) {
        $encontrado = true;
        echo "<div style='background-color: #ccffcc; padding: 10px; border: 1px solid #00cc00; margin: 10px 0;'>";
        echo "<h4>EXPEDIENTE ENCONTRADO: " . $resultado['nroexpediente'] . "</h4>";
        echo "<p><strong>Datos del expediente:</strong></p>";
        echo "<pre>" . htmlspecialchars(json_encode($resultado['data'], JSON_PRETTY_PRINT)) . "</pre>";
        echo "</div>";
        break;
    }
    
    echo "<hr>";
}

if (!$encontrado) {
    echo "<div style='background-color: #ffcccc; padding: 10px; border: 1px solid #cc0000;'>";
    echo "<h3>EXPEDIENTE NO ENCONTRADO</h3>";
    echo "<p>El expediente <strong>520-000942-1997</strong> no existe en el sistema SIGED.</p>";
    echo "<p><strong>Posibles soluciones:</strong></p>";
    echo "<ul>";
    echo "<li>Verificar que el número de expediente esté correcto</li>";
    echo "<li>Verificar que el expediente haya sido ingresado en SIGED</li>";
    echo "<li>Probar con diferentes formatos del número</li>";
    echo "</ul>";
    echo "</div>";
}

// También probar con formato sin ceros
echo "<h2>PROBANDO SIN FORMATO DE CEROS:</h2>";
$num_exp_sin_ceros = $num_exp_raw; // 942 sin formatear

foreach ($sufijos as $sufijo) {
    $nroexpediente_sin_ceros = "{$reparticion}-{$num_exp_sin_ceros}-{$ano}-{$sufijo}";
    echo "<p>Probando también: <strong>$nroexpediente_sin_ceros</strong></p>";
}
?>