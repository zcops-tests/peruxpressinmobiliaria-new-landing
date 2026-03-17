<?php
// Configuración de Cabeceras CORS (Seguridad Frontend-Backend)
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Si es una petición pre-flight de CORS, responder 200 inmediatamente
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  try {
    // 1. Honeypot - Protección Anti-Bots
    if (!empty($_POST["web_site_url"])) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Petición bloqueada por reglas de seguridad."]);
        exit;
    }

    // 2. Sanitización estricta para mitigar XSS / Inyección HTML
    $nombre = htmlspecialchars(strip_tags(trim($_POST["nombre"] ?? '')), ENT_QUOTES, 'UTF-8');
    $telefono = htmlspecialchars(strip_tags(trim($_POST["telefono"] ?? '')), ENT_QUOTES, 'UTF-8');
    $tipo_propiedad = htmlspecialchars(strip_tags(trim($_POST["tipo_propiedad"] ?? 'No especificado')), ENT_QUOTES, 'UTF-8');
    $distrito = htmlspecialchars(strip_tags(trim($_POST["distrito"] ?? 'No especificado')), ENT_QUOTES, 'UTF-8');
    $operacion = htmlspecialchars(strip_tags(trim($_POST["operacion"] ?? 'No especificado')), ENT_QUOTES, 'UTF-8');
    $precio = htmlspecialchars(strip_tags(trim($_POST["precio"] ?? 'No especificado')), ENT_QUOTES, 'UTF-8');

    // 3. Validación de requeridos y límites de longitud de desbordamiento (Buffer)
    if (empty($nombre) || empty($telefono)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Faltan datos requeridos (nombre o teléfono)."]);
        exit;
    }

    if (mb_strlen($nombre, 'UTF-8') > 100 || mb_strlen($telefono, 'UTF-8') > 20) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Los datos exceden la longitud permitida."]);
        exit;
    }

    // 4. Validación Regex estructural del teléfono (Solo números, espacios y signos + -)
    if (!preg_match('/^[0-9\+\-\s]{7,20}$/', $telefono)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "El formato del teléfono es inválido."]);
        exit;
    }

    // 5. Configuración de API y Correos
    require_once(__DIR__ . '/config.php');

    $destinatario = EMAIL_DESTINATARIO;
    $remitente = EMAIL_REMITENTE;
    $asunto = "Nuevo Lead: $nombre";

    $html_mensaje = "
    <h2>Has recibido un nuevo Lead.</h2>
    <p><strong>Detalles del Lead:</strong></p>
    <hr>
    <ul>
        <li><strong>Nombre:</strong> $nombre</li>
        <li><strong>Teléfono:</strong> $telefono</li>
        <li><strong>Tipo de Propiedad:</strong> $tipo_propiedad</li>
        <li><strong>Distrito:</strong> $distrito</li>
        <li><strong>Operación:</strong> $operacion</li>
        <li><strong>Precio Estimado:</strong> $precio</li>
    </ul>
    <hr>
    ";

    // 6. Construir el Payload JSON para EnvialoSimple
    $payload = json_encode([
        "to" => "Sistemas PeruXpress <$destinatario>",
        "from" => "PeruXpress Web <$remitente>",
        "subject" => $asunto,
        "html" => $html_mensaje
    ]);

    // 7. Ejecutar petición cURL hacia la API
    $ch = curl_init('https://api.envialosimple.email/api/v1/mail/send');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . DONWEB_API_KEY
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    // 8. Evaluar respuesta de la API (EnvialoSimple devuelve HTTP 201 en éxito)
    if ($http_code >= 200 && $http_code < 300) {
        http_response_code(200);
        echo json_encode(["status" => "success", "message" => "Correo enviado vía API."]);
    } else {
        http_response_code(500);
        echo json_encode([
            "status" => "error",
            "message" => "Error del proveedor (HTTP $http_code).",
            "debug" => $curl_error ? $curl_error : $response
        ]);
    }
  } catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Error interno: " . $e->getMessage()]);
  }
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["status" => "error", "message" => "Método HTTP no permitido."]);
}
?>