<?php
header('Content-Type: application/json');

// Habilitar CORS si es necesario
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejo de preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Habilitar errores para debugging (comentar en producción)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'conectar_bd.php';

// Verificar conexión a la base de datos
if (!$conn) {
    echo json_encode([
        'error' => true,
        'mensaje' => 'Error de conexión a la base de datos',
        'dentro_limite' => false
    ]);
    exit;
}

// Obtener parámetros
$x = isset($_POST['x']) ? floatval($_POST['x']) : (isset($_GET['x']) ? floatval($_GET['x']) : null);
$y = isset($_POST['y']) ? floatval($_POST['y']) : (isset($_GET['y']) ? floatval($_GET['y']) : null);

// Validar parámetros
if ($x === null || $y === null || $x <= 0 || $y <= 0) {
    echo json_encode([
        'error' => true,
        'mensaje' => 'Coordenadas inválidas. Se requieren valores X e Y positivos.',
        'dentro_limite' => false
    ]);
    exit;
}

try {
    // Consulta para verificar el estado del punto:
    // 1. Dentro del límite provincial
    // 2. Dentro de la zona de tolerancia (100km fuera del límite)
    // 3. Fuera de la zona permitida
    $sql = "
        WITH limite_expandido AS (
            SELECT ST_Buffer(geom, 100000) as geom_expandida, geom as geom_original
            FROM registro_grafico.gra_cs_limite_sanjuan_pga07 
            WHERE geom IS NOT NULL
            LIMIT 1
        )
        SELECT 
            ST_Contains(geom_original, ST_SetSRID(ST_MakePoint($1, $2), 5344)) as dentro_limite,
            ST_Contains(geom_expandida, ST_SetSRID(ST_MakePoint($1, $2), 5344)) as dentro_zona_tolerancia,
            ST_Distance(geom_original, ST_SetSRID(ST_MakePoint($1, $2), 5344)) as distancia_metros
        FROM limite_expandido
    ";
    
    // Ejecutar consulta con parámetros preparados para evitar SQL injection
    $result = pg_query_params($conn, $sql, [$x, $y]);
    
    if (!$result) {
        throw new Exception('Error en la consulta: ' . pg_last_error($conn));
    }
    
    $row = pg_fetch_assoc($result);
    
    if ($row === false) {
        // No se encontró el límite provincial en la base de datos
        echo json_encode([
            'error' => true,
            'mensaje' => 'No se encontró información del límite provincial en la base de datos',
            'dentro_limite' => false,
            'estado_validacion' => 'error'
        ]);
    } else {
        // Analizar el estado del punto
        $dentro_limite = ($row['dentro_limite'] === 't' || $row['dentro_limite'] === true);
        $dentro_zona_tolerancia = ($row['dentro_zona_tolerancia'] === 't' || $row['dentro_zona_tolerancia'] === true);
        $distancia = floatval($row['distancia_metros']);
        
        // Determinar estado y mensaje
        if ($dentro_limite) {
            $estado = 'dentro_limite';
            $mensaje = 'Punto dentro del límite provincial';
            $valido = true;
            $color = 'green';
        } elseif ($dentro_zona_tolerancia) {
            $estado = 'zona_tolerancia';
            $distancia_km = round($distancia / 1000, 2);
            $mensaje = "Punto fuera del límite provincial (${distancia_km} km) - Zona de tolerancia";
            $valido = true;
            $color = 'orange';
        } else {
            $estado = 'fuera_zona';
            $distancia_km = round($distancia / 1000, 2);
            $mensaje = "Punto muy alejado del límite provincial (${distancia_km} km) - No permitido";
            $valido = false;
            $color = 'red';
        }
        
        // Respuesta exitosa
        echo json_encode([
            'error' => false,
            'mensaje' => $mensaje,
            'dentro_limite' => $dentro_limite,
            'valido' => $valido,
            'estado_validacion' => $estado,
            'color' => $color,
            'distancia_km' => round($distancia / 1000, 2),
            'coordenadas' => [
                'x' => $x,
                'y' => $y
            ]
        ]);
    }
    
    // Liberar resultado
    pg_free_result($result);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => true,
        'mensaje' => 'Error al validar el punto: ' . $e->getMessage(),
        'dentro_limite' => false,
        'estado_validacion' => 'error'
    ]);
}

// Cerrar conexión
pg_close($conn);
?>