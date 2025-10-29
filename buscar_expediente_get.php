<?php
if (!isset($_GET['reparticion'], $_GET['num_exp'], $_GET['ano'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan parámetros']);
    exit;
}

$num_exp_raw = trim($_GET['num_exp'] ?? '');
$reparticion = strtoupper(trim($_GET['reparticion'] ?? ''));
$num_exp = str_pad($num_exp_raw, 6, "0", STR_PAD_LEFT);
$ano = trim($_GET['ano'] ?? '');

function consultar_expediente($sufijo) {
    global $reparticion, $num_exp, $ano;

    $nroexpediente = "{$reparticion}-{$num_exp}-{$ano}-{$sufijo}";
    $datos = ["nroexpediente" => $nroexpediente];
    $jsonDatos = json_encode($datos);

    $url = "https://soa.sanjuan.gob.ar/ConsultaExpCatastroMineria";
    $usuario = "16666407.DNI.M.ws_exp_catastro_mineria";
    $password = "DD756BFA3F3F25E4";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDatos);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    curl_setopt($ch, CURLOPT_USERPWD, "$usuario:$password");

    $respuesta = curl_exec($ch);
    curl_close($ch);

    return [
        "respuesta_json" => json_decode($respuesta, true),
        "nroexpediente" => $nroexpediente
    ];
}


// Primer intento: EXP
$res1 = consultar_expediente("EXP");
$data1 = $res1["respuesta_json"];

if (!empty($data1["expediente"])) {
    $data1["expediente"]["nroexpediente_usado"] = $res1["nroexpediente"];
    header('Content-Type: application/json');
    echo json_encode($data1);
    exit;
}

// Segundo intento: EXP-CATEO
$res2 = consultar_expediente("EXP-CATEO");
$data2 = $res2["respuesta_json"];

if (!empty($data2["expediente"])) {
    $data2["expediente"]["nroexpediente_usado"] = $res2["nroexpediente"];
    header('Content-Type: application/json');
    echo json_encode($data2);
    exit;
}

// Tercer intento: HIST
$res3 = consultar_expediente("HIST");
$data3 =$res3["respuesta_json"];

if (!empty($data3["expediente"])) {
    $data3["expediente"]["nroexpediente_usado"] = $res3["nroexpediente"];
    header('Content-Type: application/json');
    echo json_encode($data3);
    exit;
}

// Cuarto intento intento: MANIF
$res4 = consultar_expediente("EXP-MANIF");
$data4 =$res4["respuesta_json"];

if (!empty($data4["expediente"])) {
    $data4["expediente"]["nroexpediente_usado"] = $res4["nroexpediente"];
    header('Content-Type: application/json');
    echo json_encode($data4);
    exit;
}

// Si ninguno devolvió expediente válido
http_response_code(404);
echo json_encode(["error" => "Expediente no encontrado."]);
?>
