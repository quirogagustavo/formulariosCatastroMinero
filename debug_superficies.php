<?php
include 'conectar_bd.php';

if (!$conn) {
    die("Error de conexión a la base de datos.");
}

$expediente = '1124-296297-1990-EXP';

echo "<h2>ANÁLISIS DE SUPERFICIES - EXPEDIENTE: $expediente</h2>";
echo "<hr>";

// 1. Verificar PERÍMETRO
echo "<h3>1. PERÍMETRO DE MENSURA</h3>";
$sql_perimetro = "
    SELECT 
        mensar_id,
        expte_siged,
        id_pol,
        sup_decla_ha as superficie_declarada,
        sup_graf_ha as superficie_registrada,
        ST_Area(geom) / 10000 as superficie_calculada_postgis,
        sup_decla_men_ha as superficie_mensura_total,
        ST_AsText(geom) as geometria_wkt
    FROM registro_grafico.gra_cm_mensura_area_pga07
    WHERE expte_siged = '$expediente'
    ORDER BY mensar_id
";

$result = pg_query($conn, $sql_perimetro);
if ($result && pg_num_rows($result) > 0) {
    while ($row = pg_fetch_assoc($result)) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>Campo</th><th>Valor</th></tr>";
        echo "<tr><td>ID Mensura</td><td>{$row['mensar_id']}</td></tr>";
        echo "<tr><td>ID Polígono</td><td>{$row['id_pol']}</td></tr>";
        echo "<tr><td>Superficie Declarada (sup_decla_ha)</td><td><b>{$row['superficie_declarada']} ha</b></td></tr>";
        echo "<tr><td>Superficie Registrada (sup_graf_ha)</td><td><b>{$row['superficie_registrada']} ha</b></td></tr>";
        echo "<tr><td>Superficie Calculada PostGIS</td><td><b>{$row['superficie_calculada_postgis']} ha</b></td></tr>";
        echo "<tr><td>Superficie Total Mensura</td><td>{$row['sup_decla_men_ha']} ha</td></tr>";
        echo "</table><br>";
        
        // Extraer vértices
        $sql_vertices = "
            SELECT ST_Y((dp).geom) as x_norte, ST_X((dp).geom) as y_este
            FROM registro_grafico.gra_cm_mensura_area_pga07 t
            JOIN LATERAL ST_DumpPoints(t.geom) AS dp ON true
            WHERE t.mensar_id = {$row['mensar_id']}
        ";
        $result_v = pg_query($conn, $sql_vertices);
        echo "<b>Vértices del perímetro:</b><br>";
        echo "<table border='1' cellpadding='3'>";
        echo "<tr><th>#</th><th>X (NORTE)</th><th>Y (ESTE)</th></tr>";
        $i = 1;
        while ($v = pg_fetch_assoc($result_v)) {
            echo "<tr><td>V$i</td><td>{$v['x_norte']}</td><td>{$v['y_este']}</td></tr>";
            $i++;
        }
        echo "</table><br>";
    }
} else {
    echo "<p style='color: red;'>No se encontró perímetro de mensura</p>";
}

echo "<hr>";

// 2. Verificar PERTENENCIAS
echo "<h3>2. PERTENENCIAS</h3>";
$sql_pertenencias = "
    SELECT 
        mens_id,
        id_pol,
        id_pert,
        sup_solic_ha as superficie_solicitada,
        sup_graf_ha as superficie_graficada,
        sup_reg_ha as superficie_registrada,
        ST_Area(geom) / 10000 as superficie_calculada_postgis,
        sup_decla_men_ha as superficie_mensura_total
    FROM registro_grafico.gra_cm_mensura_pertenencias_pga07
    WHERE expte_siged = '$expediente'
    ORDER BY mens_id, id_pert
";

$result2 = pg_query($conn, $sql_pertenencias);
$suma_pertenencias = 0;

if ($result2 && pg_num_rows($result2) > 0) {
    while ($row = pg_fetch_assoc($result2)) {
        $sup_calc = floatval($row['superficie_calculada_postgis']);
        $suma_pertenencias += $sup_calc;
        
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse; margin-bottom: 15px;'>";
        echo "<tr><th colspan='2' style='background-color: #cce5ff;'>Pertenencia {$row['id_pert']}</th></tr>";
        echo "<tr><td>ID Mensura</td><td>{$row['mens_id']}</td></tr>";
        echo "<tr><td>ID Polígono</td><td>{$row['id_pol']}</td></tr>";
        echo "<tr><td>Superficie Solicitada (sup_solic_ha)</td><td>{$row['superficie_solicitada']} ha</td></tr>";
        echo "<tr><td>Superficie Graficada (sup_graf_ha)</td><td>{$row['superficie_graficada']} ha</td></tr>";
        echo "<tr><td>Superficie Registrada (sup_reg_ha)</td><td><b>{$row['superficie_registrada']} ha</b></td></tr>";
        echo "<tr><td>Superficie Calculada PostGIS</td><td><b style='color: blue;'>{$sup_calc} ha</b></td></tr>";
        echo "<tr><td>Superficie Total Mensura</td><td>{$row['superficie_mensura_total']} ha</td></tr>";
        echo "</table>";
        
        // Extraer vértices de pertenencia
        $sql_vert_pert = "
            SELECT ST_Y((dp).geom) as x_norte, ST_X((dp).geom) as y_este
            FROM registro_grafico.gra_cm_mensura_pertenencias_pga07 t
            JOIN LATERAL ST_DumpPoints(t.geom) AS dp ON true
            WHERE t.mens_id = {$row['mens_id']} AND t.id_pert = '{$row['id_pert']}'
        ";
        $result_vp = pg_query($conn, $sql_vert_pert);
        echo "<b>Vértices de {$row['id_pert']}:</b><br>";
        echo "<table border='1' cellpadding='3'>";
        echo "<tr><th>#</th><th>X (NORTE)</th><th>Y (ESTE)</th></tr>";
        $j = 1;
        while ($vp = pg_fetch_assoc($result_vp)) {
            echo "<tr><td>V$j</td><td>{$vp['x_norte']}</td><td>{$vp['y_este']}</td></tr>";
            $j++;
        }
        echo "</table><br><br>";
    }
    
    echo "<hr>";
    echo "<h3>3. RESUMEN Y VALIDACIÓN</h3>";
    echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
    echo "<tr><th>Concepto</th><th>Valor</th></tr>";
    
    // Obtener superficie del perímetro
    pg_result_seek($result, 0);
    $perimetro = pg_fetch_assoc($result);
    $sup_perimetro = floatval($perimetro['superficie_calculada_postgis']);
    
    echo "<tr><td><b>Superficie Perímetro (PostGIS)</b></td><td style='font-size: 16px;'><b>$sup_perimetro ha</b></td></tr>";
    echo "<tr><td><b>Suma Pertenencias (PostGIS)</b></td><td style='font-size: 16px;'><b>$suma_pertenencias ha</b></td></tr>";
    
    $diferencia = $suma_pertenencias - $sup_perimetro;
    $porcentaje = ($suma_pertenencias / $sup_perimetro) * 100;
    
    $color = ($diferencia > 0) ? 'red' : 'green';
    echo "<tr><td>Diferencia</td><td style='color: $color; font-weight: bold;'>$diferencia ha</td></tr>";
    echo "<tr><td>Porcentaje Utilizado</td><td><b>{$porcentaje}%</b></td></tr>";
    
    if ($diferencia > 0) {
        echo "<tr><td colspan='2' style='background-color: #ffcccc; padding: 10px;'>";
        echo "<b>⚠️ ERROR DETECTADO:</b><br>";
        echo "La suma de pertenencias EXCEDE el perímetro en <b>$diferencia ha</b><br>";
        echo "Esto es técnicamente imposible.";
        echo "</td></tr>";
    } else {
        echo "<tr><td colspan='2' style='background-color: #ccffcc; padding: 10px;'>";
        echo "✅ Las superficies son consistentes";
        echo "</td></tr>";
    }
    
    echo "</table>";
    
} else {
    echo "<p style='color: red;'>No se encontraron pertenencias</p>";
}

pg_close($conn);
?>
