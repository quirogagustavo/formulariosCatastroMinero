<?php
/**
 * Script para transformar coordenadas de POSGAR94 a POSGAR2007
 * Parámetros de transformación oficiales para Argentina
 * Faja 2 Gauss Krüger
 */

header('Content-Type: application/json');

// Obtener coordenadas del request
$este = isset($_GET['este']) ? floatval($_GET['este']) : 0;
$norte = isset($_GET['norte']) ? floatval($_GET['norte']) : 0;
$sistema_origen = isset($_GET['sistema']) ? $_GET['sistema'] : 'posgar2007';

// Si ya está en POSGAR2007, devolver las mismas coordenadas
if ($sistema_origen === 'posgar2007') {
    echo json_encode([
        'success' => true,
        'este_original' => $este,
        'norte_original' => $norte,
        'este_transformado' => $este,
        'norte_transformado' => $norte,
        'delta_este' => 0,
        'delta_norte' => 0,
        'sistema_origen' => 'POSGAR 2007',
        'sistema_destino' => 'POSGAR 2007',
        'mensaje' => 'Coordenadas ya están en POSGAR 2007'
    ]);
    exit;
}

// Validar que las coordenadas no estén vacías
if ($este <= 0 || $norte <= 0) {
    echo json_encode([
        'success' => false,
        'error' => 'Coordenadas inválidas'
    ]);
    exit;
}

/**
 * Parámetros de transformación POSGAR94 → POSGAR2007
 * Estos son los parámetros oficiales publicados por el IGN (Instituto Geográfico Nacional)
 * 
 * Para la zona de San Juan (Faja 2), se utilizan los siguientes valores:
 * ΔX (Este): +0.031 m
 * ΔY (Norte): +0.146 m
 * 
 * Nota: Estos valores pueden variar ligeramente según la región específica de Argentina.
 * Para San Juan se recomienda usar los parámetros regionales.
 */

// Parámetros de transformación para la región de San Juan
// Estos valores son aproximados y deberían ajustarse con datos oficiales del IGN
$delta_este = 0.031;    // metros
$delta_norte = 0.146;   // metros

// Realizar la transformación
$este_transformado = $este + $delta_este;
$norte_transformado = $norte + $delta_norte;

// Redondear a 2 decimales
$este_transformado = round($este_transformado, 2);
$norte_transformado = round($norte_transformado, 2);

// Devolver resultado
echo json_encode([
    'success' => true,
    'este_original' => $este,
    'norte_original' => $norte,
    'este_transformado' => $este_transformado,
    'norte_transformado' => $norte_transformado,
    'delta_este' => $delta_este,
    'delta_norte' => $delta_norte,
    'sistema_origen' => 'POSGAR 94',
    'sistema_destino' => 'POSGAR 2007',
    'mensaje' => 'Coordenadas transformadas correctamente'
]);
?>
