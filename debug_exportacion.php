<?php

/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/

header("Content-Type: text/html; charset=UTF-8");

include 'conectar_bd.php';

if (!$conn) {
    die("Error de conexión a la base de datos.");
}

// Inicializar búsqueda
$busqueda_expte = $_GET['expte'] ?? '1124-000253-2013-EXP';

echo "<h2>DEBUG EXPORTACIÓN - Expediente: $busqueda_expte</h2>";

if ($busqueda_expte !== '') {
    $params = array('%' . $busqueda_expte . '%');

    // Pertenencias
    $sql2 = "
         SELECT 
            t.expte_siged,
            t.id_pol,
            t.sup_reg_ha,
            t.sup_decla_men_ha,
            t.mens_id,
            t.id_pert,
            t.geom AS geom_original,
            json_agg(json_build_object('x', ST_X((dp).geom), 'y', ST_Y((dp).geom))) AS vertices
        FROM registro_grafico.gra_cm_mensura_pertenencias_pga07 t
        JOIN LATERAL ST_DumpPoints(t.geom) AS dp ON true
        WHERE t.expte_siged ILIKE $1
        GROUP BY t.expte_siged, t.id_pol, t.sup_reg_ha, t.sup_decla_men_ha, t.mens_id, t.id_pert, t.geom
        ORDER BY t.id_pert::int ASC
    ";

    $result2 = pg_query_params($conn, $sql2, $params);
    
    if ($result2) {
        echo "<h3>PROCESO DE EXPORTACIÓN - DEBUG</h3>";
        
        $contador_procesadas = 0;
        $contador_con_errores = 0;
        
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID Pert</th><th>Status</th><th>Vértices</th><th>Problema</th></tr>";
        
        while ($row2 = pg_fetch_assoc($result2)) {
            $contador_procesadas++;
            $status = "OK";
            $problema = "";
            
            // Intentar decodificar vertices
            $vertices = json_decode($row2['vertices'], true);
            
            // Validar que json_decode funcionó correctamente
            if ($vertices === null || !is_array($vertices) || empty($vertices)) {
                $status = "ERROR";
                $problema = "JSON inválido o vértices vacíos";
                $contador_con_errores++;
            } else {
                $ultimo = count($vertices) - 1;
                
                // Verificar validez de vértices
                if ($ultimo > 0 && isset($vertices[$ultimo]) && isset($vertices[0])) {
                    if ($vertices[$ultimo]['x'] == $vertices[0]['x'] && $vertices[$ultimo]['y'] == $vertices[0]['y']) {
                        array_pop($vertices); // saco el último duplicado
                    }
                }
                
                // Verificar vértices individuales
                foreach ($vertices as $i => $v) {
                    if (!isset($v['x']) || !isset($v['y'])) {
                        $status = "ERROR";
                        $problema = "Vértice $i sin coordenadas válidas";
                        $contador_con_errores++;
                        break;
                    }
                }
            }
            
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row2['id_pert']) . "</td>";
            echo "<td style='color: " . ($status == "OK" ? "green" : "red") . ";'>" . $status . "</td>";
            echo "<td>" . (is_array($vertices) ? count($vertices) : "N/A") . "</td>";
            echo "<td>" . $problema . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        echo "<br>";
        echo "<p><strong>Total de pertenencias procesadas:</strong> $contador_procesadas</p>";
        echo "<p><strong>Pertenencias con errores:</strong> $contador_con_errores</p>";
        echo "<p><strong>Pertenencias que se mostrarían correctamente:</strong> " . ($contador_procesadas - $contador_con_errores) . "</p>";
        
        pg_free_result($result2);
    } else {
        echo "Error en consulta: " . pg_last_error($conn);
    }
}

pg_close($conn);
?>