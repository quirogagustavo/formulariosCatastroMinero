<?php
include 'conectar_bd.php';

if (!$conn) {
    die("Error de conexión a la base de datos.");
}

echo "<h1>ANÁLISIS DE EXPEDIENTES DUPLICADOS/SIMILARES</h1>";

// 1. Buscar todos los expedientes que contengan 253 y 2013
echo "<h2>1. EXPEDIENTES CON '253' Y '2013':</h2>";
$sql_buscar = "SELECT DISTINCT expte_siged, COUNT(*) as pertenencias 
               FROM registro_grafico.gra_cm_mensura_pertenencias_pga07 
               WHERE expte_siged ILIKE '%253%' AND expte_siged ILIKE '%2013%'
               GROUP BY expte_siged
               ORDER BY expte_siged";

$result_buscar = pg_query($conn, $sql_buscar);

echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>Expediente Completo</th><th>Pertenencias</th><th>Acciones</th></tr>";

$expedientes_encontrados = [];
while ($row = pg_fetch_assoc($result_buscar)) {
    $expediente = $row['expte_siged'];
    $pertenencias = $row['pertenencias'];
    $expedientes_encontrados[] = $expediente;
    
    echo "<tr>";
    echo "<td><strong>" . htmlspecialchars($expediente) . "</strong></td>";
    echo "<td>" . $pertenencias . "</td>";
    echo "<td>";
    echo "<a href='exportar_solicitud_peticion_mensura.php?expte=" . urlencode($expediente) . "' target='_blank'>Exportar</a> | ";
    echo "<a href='reporte_solicitud_peticion_mensura.php?expte=" . urlencode($expediente) . "' target='_blank'>Reporte</a>";
    echo "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<p><strong>Total de expedientes encontrados: " . count($expedientes_encontrados) . "</strong></p>";

// 2. Analizar las diferencias específicas
echo "<h2>2. ANÁLISIS DETALLADO DE CADA EXPEDIENTE:</h2>";

foreach ($expedientes_encontrados as $expediente) {
    echo "<h3>Expediente: " . htmlspecialchars($expediente) . "</h3>";
    
    // Listar todas las pertenencias de este expediente
    $sql_pertenencias = "SELECT id_pert, id_pol, sup_reg_ha 
                         FROM registro_grafico.gra_cm_mensura_pertenencias_pga07 
                         WHERE expte_siged = $1 
                         ORDER BY id_pert::int ASC";
    
    $result_pertenencias = pg_query_params($conn, $sql_pertenencias, [$expediente]);
    
    echo "<table border='1' style='border-collapse: collapse; margin-left: 20px;'>";
    echo "<tr><th>ID Pert</th><th>ID Pol</th><th>Superficie</th></tr>";
    
    $ids_pertenencias = [];
    while ($pert = pg_fetch_assoc($result_pertenencias)) {
        $ids_pertenencias[] = $pert['id_pert'];
        echo "<tr>";
        echo "<td>" . $pert['id_pert'] . "</td>";
        echo "<td>" . $pert['id_pol'] . "</td>";
        echo "<td>" . $pert['sup_reg_ha'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<p><strong>IDs de pertenencias: " . implode(', ', $ids_pertenencias) . "</strong></p>";
    echo "<p><strong>Total: " . count($ids_pertenencias) . " pertenencias</strong></p>";
    
    // Verificar si hay huecos en la secuencia
    $huecos = [];
    if (!empty($ids_pertenencias)) {
        $min_id = min(array_map('intval', $ids_pertenencias));
        $max_id = max(array_map('intval', $ids_pertenencias));
        
        for ($i = $min_id; $i <= $max_id; $i++) {
            if (!in_array((string)$i, $ids_pertenencias)) {
                $huecos[] = $i;
            }
        }
        
        if (!empty($huecos)) {
            echo "<p style='color: red;'><strong>Huecos en secuencia: " . implode(', ', $huecos) . "</strong></p>";
        } else {
            echo "<p style='color: green;'><strong>Secuencia continua desde $min_id hasta $max_id</strong></p>";
        }
    }
    
    echo "<hr>";
}

// 3. Buscar expedientes en otras tablas relacionadas
echo "<h2>3. VERIFICAR EN TABLA PRINCIPAL DE MENSURA:</h2>";
$sql_principal = "SELECT expte_siged, denom, depto 
                  FROM registro_grafico.gra_cm_mensura_area_pga07 
                  WHERE expte_siged ILIKE '%253%' AND expte_siged ILIKE '%2013%'
                  ORDER BY expte_siged";

$result_principal = pg_query($conn, $sql_principal);

echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>Expediente</th><th>Denominación</th><th>Departamento</th></tr>";

while ($row = pg_fetch_assoc($result_principal)) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['expte_siged']) . "</td>";
    echo "<td>" . htmlspecialchars($row['denom']) . "</td>";
    echo "<td>" . htmlspecialchars($row['depto']) . "</td>";
    echo "</tr>";
}
echo "</table>";

// 4. Sugerencia de unificación
echo "<h2>4. RECOMENDACIÓN:</h2>";
if (count($expedientes_encontrados) > 1) {
    echo "<div style='background-color: #ffffcc; padding: 10px; border: 1px solid #cccc00;'>";
    echo "<p><strong>Se encontraron múltiples variaciones del mismo expediente.</strong></p>";
    echo "<p>Para solucionar el problema:</p>";
    echo "<ol>";
    echo "<li>Verificar cuál es el formato correcto del expediente</li>";
    echo "<li>Unificar todos los registros bajo un mismo número</li>";
    echo "<li>Actualizar las referencias en la base de datos</li>";
    echo "</ol>";
    echo "</div>";
} else {
    echo "<p style='color: green;'>Solo se encontró una variación del expediente.</p>";
}

pg_close($conn);
?>