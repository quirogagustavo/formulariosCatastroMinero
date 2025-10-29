<?php
// Script directo para verificar expediente en tablas específicas
$expediente = "520-000942-1997-EXP";

// Configuración de conexión (usando la misma que conectar_bd.php)
$host = "10.2.165.196";
$port = "5432"; 
$dbname = "catastrominero";
$user = "catastro";
$password = "KHy1G=gnK";

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== VERIFICACIÓN EXPEDIENTE $expediente ===\n\n";
    
    // 1. Verificar en gra_cm_manifestaciones_pga07
    echo "1. Buscando en gra_cm_manifestaciones_pga07:\n";
    $stmt1 = $pdo->prepare("SELECT expte_siged, denom, superficie FROM registro_grafico.gra_cm_manifestaciones_pga07 WHERE expte_siged = ?");
    $stmt1->execute([$expediente]);
    $result1 = $stmt1->fetchAll(PDO::FETCH_ASSOC);
    
    if ($result1) {
        echo "   ✓ ENCONTRADO en manifestaciones:\n";
        foreach ($result1 as $row) {
            echo "     - Expediente: {$row['expte_siged']}\n";
            echo "     - Denominación: {$row['denom']}\n";
            echo "     - Superficie: {$row['superficie']}\n";
        }
    } else {
        echo "   ✗ NO encontrado en manifestaciones\n";
    }
    
    // 2. Verificar en gra_cm_minas_pga07
    echo "\n2. Buscando en gra_cm_minas_pga07:\n";
    $stmt2 = $pdo->prepare("SELECT expte_siged, denom, superficie FROM registro_grafico.gra_cm_minas_pga07 WHERE expte_siged = ?");
    $stmt2->execute([$expediente]);
    $result2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    
    if ($result2) {
        echo "   ✓ ENCONTRADO en minas:\n";
        foreach ($result2 as $row) {
            echo "     - Expediente: {$row['expte_siged']}\n";
            echo "     - Denominación: {$row['denom']}\n";
            echo "     - Superficie: {$row['superficie']}\n";
        }
    } else {
        echo "   ✗ NO encontrado en minas\n";
    }
    
    // 3. Búsqueda por patrón
    echo "\n3. Búsqueda por patrón '520-000942-1997%':\n";
    
    $stmt3 = $pdo->prepare("SELECT expte_siged, denom FROM registro_grafico.gra_cm_manifestaciones_pga07 WHERE expte_siged LIKE ?");
    $stmt3->execute(["520-000942-1997%"]);
    $result3 = $stmt3->fetchAll(PDO::FETCH_ASSOC);
    
    echo "   En manifestaciones:\n";
    if ($result3) {
        foreach ($result3 as $row) {
            echo "     - {$row['expte_siged']} - {$row['denom']}\n";
        }
    } else {
        echo "     Ningún resultado\n";
    }
    
    $stmt4 = $pdo->prepare("SELECT expte_siged, denom FROM registro_grafico.gra_cm_minas_pga07 WHERE expte_siged LIKE ?");
    $stmt4->execute(["520-000942-1997%"]);
    $result4 = $stmt4->fetchAll(PDO::FETCH_ASSOC);
    
    echo "   En minas:\n";
    if ($result4) {
        foreach ($result4 as $row) {
            echo "     - {$row['expte_siged']} - {$row['denom']}\n";
        }
    } else {
        echo "     Ningún resultado\n";
    }
    
} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage() . "\n";
    echo "\nIntentando con pg_connect...\n";
    
    // Método alternativo con pg_connect
    $connection_string = "host=$host port=$port dbname=$dbname user=$user password=$password";
    $db = pg_connect($connection_string);
    
    if (!$db) {
        echo "Error: No se puede conectar a la base de datos\n";
        exit;
    }
    
    echo "Conexión exitosa con pg_connect\n";
    
    // Repetir consultas con pg_query
    echo "\n=== USANDO PG_CONNECT ===\n";
    
    $query1 = "SELECT expte_siged, denom, superficie FROM registro_grafico.gra_cm_manifestaciones_pga07 WHERE expte_siged = '$expediente'";
    $result1 = pg_query($db, $query1);
    
    echo "1. Manifestaciones:\n";
    if ($result1 && pg_num_rows($result1) > 0) {
        while ($row = pg_fetch_assoc($result1)) {
            echo "   - {$row['expte_siged']} - {$row['denom']}\n";
        }
    } else {
        echo "   No encontrado\n";
    }
    
    $query2 = "SELECT expte_siged, denom, superficie FROM registro_grafico.gra_cm_minas_pga07 WHERE expte_siged = '$expediente'";
    $result2 = pg_query($db, $query2);
    
    echo "2. Minas:\n";
    if ($result2 && pg_num_rows($result2) > 0) {
        while ($row = pg_fetch_assoc($result2)) {
            echo "   - {$row['expte_siged']} - {$row['denom']}\n";
        }
    } else {
        echo "   No encontrado\n";
    }
    
    pg_close($db);
}

echo "\n=== FIN VERIFICACIÓN ===\n";
?>