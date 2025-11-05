<?php
// Coordenadas del documento
$vertices = [
    ['x' => 6677723.20, 'y' => 2492370.69, 'nombre' => 'V1'],
    ['x' => 6677567.34, 'y' => 2493358.51, 'nombre' => 'V2'],
    ['x' => 6676579.40, 'y' => 2493203.90, 'nombre' => 'V3'],
    ['x' => 6676735.38, 'y' => 2492215.05, 'nombre' => 'V4']
];

echo '<h2>🔍 Verificación de Orden de Vértices</h2>';
echo '<h3>Coordenadas del Documento:</h3>';
echo '<table border="1" cellpadding="5">';
echo '<tr><th>Vértice</th><th>X (ESTE)</th><th>Y (NORTE)</th></tr>';
foreach ($vertices as $v) {
    echo '<tr><td>' . $v['nombre'] . '</td><td>' . $v['x'] . '</td><td>' . $v['y'] . '</td></tr>';
}
echo '</table>';

// Calcular área con signo (Shoelace formula)
$area = 0;
$n = count($vertices);
for ($i = 0; $i < $n; $i++) {
    $j = ($i + 1) % $n;
    $area += $vertices[$i]['x'] * $vertices[$j]['y'];
    $area -= $vertices[$j]['x'] * $vertices[$i]['y'];
}
$area = $area / 2;

echo '<h3>Cálculo del Área con Signo:</h3>';
echo '<p><strong>Área calculada:</strong> ' . number_format($area, 2, '.', ',') . ' m²</p>';

if ($area > 0) {
    echo '<p style="color:red; font-size:18px;">❌ <strong>ANTIHORARIO</strong> (área positiva)</p>';
    echo '<p>Los vértices están en sentido contrario a las agujas del reloj.</p>';
} else {
    echo '<p style="color:green; font-size:18px;">✅ <strong>HORARIO</strong> (área negativa)</p>';
    echo '<p>Los vértices están en sentido de las agujas del reloj.</p>';
}

echo '<h3>Para corregir a HORARIO:</h3>';
echo '<p>Ingresar los vértices en este orden:</p>';
echo '<table border="1" cellpadding="5">';
echo '<tr><th>Nuevo Orden</th><th>Vértice</th><th>X (ESTE)</th><th>Y (NORTE)</th></tr>';

// Invertir el orden para hacerlo horario
$vertices_invertidos = array_reverse($vertices);
foreach ($vertices_invertidos as $idx => $v) {
    echo '<tr><td>Paso ' . ($idx + 1) . '</td><td>' . $v['nombre'] . '</td><td>' . $v['x'] . '</td><td>' . $v['y'] . '</td></tr>';
}
echo '</table>';
?>
