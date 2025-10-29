<?php
if (!isset($_GET['cuit'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Falta el parámetro cuit']);
    exit;
}

$cuit = preg_replace('/\D/', '', $_GET['cuit']); // solo dígitos

$url = "https://www.cuitonline.com/search/$cuit";

$opts = [
    "http" => [
        "method" => "GET",
        "header" => "User-Agent: Mozilla/5.0"
    ]
];

$context = stream_context_create($opts);
$html = file_get_contents($url, false, $context);

if (!$html) {
    http_response_code(500);
    echo json_encode(['error' => 'No se pudo acceder al sitio']);
    exit;
}

preg_match('/<h2 class="denominacion".*?>(.*?)<\/h2>/si', $html, $matches);

if (isset($matches[1])) {
    $nombre = trim(strip_tags($matches[1]));
} else {
    $nombre = null;
}

if (substr($cuit, 0, 2) === "00") {
    $tipo = 'Extranjero';
} else {
    $tipo = '';
    if (stripos($html, 'Persona&nbsp;Física') !== false) {
    $tipo = 'Persona Física';
    } elseif (stripos($html, 'Persona&nbsp;Jurídica') !== false) {
    $tipo = 'Persona Jurídica';
    }
}

echo json_encode(['nombre' => $nombre, 'tipo' => $tipo]);
?>