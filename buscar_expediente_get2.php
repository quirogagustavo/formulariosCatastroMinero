<?php
//Buscamos en manifestaciones o minas en este caso
include 'conectar_bd.php';

// Validar parámetros por GET
if (!isset($_GET['reparticion'], $_GET['num_exp'], $_GET['ano'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan parámetros']);
    exit;
}

$num_exp_raw = trim($_GET['num_exp'] ?? '');
$reparticion = strtoupper(trim($_GET['reparticion'] ?? ''));
$num_exp = str_pad($num_exp_raw, 6, "0", STR_PAD_LEFT);
$ano = trim($_GET['ano'] ?? '');

if (!$db) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit;
}

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

function expediente_existe_en_bd($db, $expte_siged) {
    $query = "
        SELECT 1 FROM registro_grafico.gra_cm_manifestaciones_pga07 WHERE expte_siged = $1
        UNION
        SELECT 1 FROM registro_grafico.gra_cm_minas_pga07 WHERE expte_siged = $1
        LIMIT 1
    ";
    $res = pg_query_params($db, $query, [$expte_siged]);
    return pg_num_rows($res) > 0;
}

// Intentos con diferentes sufijos
foreach (['EXP', 'EXP-CATEO', 'HIST', 'EXP-MANIF'] as $sufijo) {
    $res = consultar_expediente($sufijo);
    $data = $res['respuesta_json'];

    if (!empty($data['expediente'])) {
        $nroexpediente = $res['nroexpediente'];
       $query = "SELECT denom AS denom
                FROM registro_grafico.gra_cm_manifestaciones_pga07
                WHERE expte_siged = $1
                UNION
                SELECT denom AS denom
                FROM registro_grafico.gra_cm_minas_pga07
                WHERE expte_siged = $1
                LIMIT 1;";

    $result = pg_query_params($db, $query, [$nroexpediente]);

        if ($result && pg_num_rows($result) > 0) {
        $row = pg_fetch_assoc($result);
        $denom = $row['denom'];  
        } else {
        $denom = ''; 
        }

    if (expediente_existe_en_bd($db, $nroexpediente)) {
            $data['expediente']['nroexpediente_usado'] = $nroexpediente;
            $data['expediente']['denom'] = $denom; 
            header('Content-Type: application/json');
            echo json_encode($data);
            exit;
        }
    }
}

// Si no se encontró en el servicio o en la base de datos
http_response_code(404);
echo json_encode(['error' => 'Expediente no encontrado en el servicio ni en la base de datos.']);
?>
