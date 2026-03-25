<?php
// ============================================
// CONEXIÓN A BASE DE DATOS
// Soporta variables de entorno para producción
// ============================================

// Cargar archivo .env si existe (se buscan dos ubicaciones posibles)
$env_paths = [
    __DIR__ . '/.env',                 // .env en la carpeta actual (formularios)
    dirname(__DIR__) . '/.env'         // .env en la carpeta superior (Catastro)
];

foreach ($env_paths as $env_file) {
    if (file_exists($env_file)) {
        $env_vars = parse_ini_file($env_file);
        if ($env_vars) {
            foreach ($env_vars as $key => $value) {
                if (!isset($_ENV[$key]) && getenv($key) === false) {
                    putenv("$key=$value");
                }
            }
        }
        // Si ya se cargó un .env, no hace falta seguir buscando
        break;
    }
}

// Detectar entorno (por defecto: production)
$app_env = getenv('APP_ENV') ?: 'production';

// Obtener credenciales desde variables de entorno
if ($app_env === 'local' || $app_env === 'development') {
    // En desarrollo exigir que vengan de .env / entorno
    $db_host = getenv('DB_HOST');
    $db_port = getenv('DB_PORT');
    $db_name = getenv('DB_NAME');
    $db_user = getenv('DB_USER');
    $db_password = getenv('DB_PASSWORD');
} else {
    // En producción usar valores por defecto si no están definidos
    $db_host = getenv('DB_HOST') ?: '10.2.165.196';
    $db_port = getenv('DB_PORT') ?: '5432';
    $db_name = getenv('DB_NAME') ?: 'catastrominero';
    $db_user = getenv('DB_USER') ?: 'catastro';
    $db_password = getenv('DB_PASSWORD') ?: '';
}

// Validar que tenemos credenciales
if (empty($db_host) || empty($db_name) || empty($db_user)) {
    error_log("Error: Falta configuración de base de datos en variables de entorno (.env)");
    if ($app_env === 'local' || $app_env === 'development') {
        die("Error: Configuración de base de datos incompleta en entorno $app_env. Verifique el archivo .env (DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASSWORD).");
    } else {
        die("Error: Configuración de base de datos incompleta");
    }
}

// Construir string de conexión
$connection_string = "host=$db_host port=$db_port dbname=$db_name user=$db_user password=$db_password";

// Conectar a PostgreSQL
// En desarrollo no ocultamos los warnings para ver el problema real
if ($app_env === 'local' || $app_env === 'development') {
    $conn = pg_connect($connection_string);
} else {
    $conn = @pg_connect($connection_string);
}

// Verificar conexión
if (!$conn) {
    $last_error = error_get_last();
    $pg_error = isset($last_error['message']) ? $last_error['message'] : 'No se pudo establecer la conexión a PostgreSQL.';
    error_log("Error: No se pudo conectar a la base de datos. Host: $db_host, DB: $db_name, User: $db_user. Detalle: $pg_error");

    if ($app_env === 'local' || $app_env === 'development') {
        // En desarrollo mostramos el detalle para poder depurar
        die("Error de conexión a la base de datos (entorno $app_env). Detalle: " . htmlspecialchars($pg_error));
    } else {
        // En producción solo mostramos un mensaje genérico
        die("Error de conexión a la base de datos. Por favor, contacte al administrador.");
    }
}

// Establecer configuración de la conexión
pg_set_client_encoding($conn, 'UTF8');

// Variable global para compatibilidad con código existente
$db = $conn;
?>