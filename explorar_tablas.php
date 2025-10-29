<?php
include 'conectar_bd.php';

echo "=== EXPLORANDO ESTRUCTURA DE TABLAS ===\n\n";

// Verificar columnas de manifestaciones
echo "1. Columnas en gra_cm_manifestaciones_pga07:\n";
$query1 = "SELECT column_name, data_type FROM information_schema.columns WHERE table_schema = 'registro_grafico' AND table_name = 'gra_cm_manifestaciones_pga07' ORDER BY ordinal_position";
$result1 = pg_query($db, $query1);

if ($result1) {
    while ($row = pg_fetch_assoc($result1)) {
        echo "   - {$row['column_name']} ({$row['data_type']})\n";
    }
} else {
    echo "   Error al consultar columnas de manifestaciones\n";
}

echo "\n2. Columnas en gra_cm_minas_pga07:\n";
$query2 = "SELECT column_name, data_type FROM information_schema.columns WHERE table_schema = 'registro_grafico' AND table_name = 'gra_cm_minas_pga07' ORDER BY ordinal_position";
$result2 = pg_query($db, $query2);

if ($result2) {
    while ($row = pg_fetch_assoc($result2)) {
        echo "   - {$row['column_name']} ({$row['data_type']})\n";
    }
} else {
    echo "   Error al consultar columnas de minas\n";
}

// Buscar cualquier registro que contenga '520-000942-1997'
echo "\n3. Buscando expedientes que contengan '520-000942-1997':\n";

echo "\n   En manifestaciones:\n";
$query3 = "SELECT * FROM registro_grafico.gra_cm_manifestaciones_pga07 WHERE expte_siged LIKE '%520-000942-1997%' LIMIT 5";
$result3 = pg_query($db, $query3);

if ($result3 && pg_num_rows($result3) > 0) {
    while ($row = pg_fetch_assoc($result3)) {
        echo "     Encontrado: ";
        foreach ($row as $key => $value) {
            echo "$key: $value | ";
        }
        echo "\n";
    }
} else {
    echo "     Ningún resultado\n";
}

echo "\n   En minas:\n";
$query4 = "SELECT * FROM registro_grafico.gra_cm_minas_pga07 WHERE expte_siged LIKE '%520-000942-1997%' LIMIT 5";
$result4 = pg_query($db, $query4);

if ($result4 && pg_num_rows($result4) > 0) {
    while ($row = pg_fetch_assoc($result4)) {
        echo "     Encontrado: ";
        foreach ($row as $key => $value) {
            echo "$key: $value | ";
        }
        echo "\n";
    }
} else {
    echo "     Ningún resultado\n";
}

echo "\n=== FIN EXPLORACIÓN ===\n";
?>