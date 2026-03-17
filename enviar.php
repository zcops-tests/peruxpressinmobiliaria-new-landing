<?php
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
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

    $destinatario = "sistemas@peruxpressinmobiliaria.com";
    $asunto = "Nuevo Lead Landing Page: $nombre";

    $mensaje = "Has recibido un nuevo contacto desde la página web.\n\n";
    $mensaje .= "Detalles del contacto:\n";
    $mensaje .= "-----------------------------------\n";
    $mensaje .= "Nombre: $nombre\n";
    $mensaje .= "Teléfono: $telefono\n";
    $mensaje .= "Tipo de Propiedad: $tipo_propiedad\n";
    $mensaje .= "Distrito: $distrito\n";
    $mensaje .= "Operación: $operacion\n";
    $mensaje .= "Precio Estimado: $precio\n";
    $mensaje .= "-----------------------------------\n";

    // Headers. Importante: "From" debe idealmente ser una cuenta de tu mismo dominio en DonWeb
    $headers = "From: no-reply@peruxpressinmobiliaria.pe\r\n";
    $headers .= "Reply-To: no-reply@peruxpressinmobiliaria.pe\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    // Envío del correo usando la función mail nativa
    if (mail($destinatario, $asunto, $mensaje, $headers)) {
        http_response_code(200);
        echo json_encode(["status" => "success", "message" => "Correo enviado correctamente."]);
    }
    else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Hubo un error al enviar el correo desde el servidor."]);
    }
}
else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["status" => "error", "message" => "Método HTTP no permitido."]);
}
?>
