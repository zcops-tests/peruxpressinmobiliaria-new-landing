<?php
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitizar y recoger datos
    $nombre = strip_tags(trim($_POST["nombre"] ?? ''));
    $telefono = strip_tags(trim($_POST["telefono"] ?? ''));
    $tipo_propiedad = strip_tags(trim($_POST["tipo_propiedad"] ?? 'No especificado'));
    $distrito = strip_tags(trim($_POST["distrito"] ?? 'No especificado'));
    $operacion = strip_tags(trim($_POST["operacion"] ?? 'No especificado'));
    $precio = strip_tags(trim($_POST["precio"] ?? 'No especificado'));

    // Validar requeridos
    if (empty($nombre) || empty($telefono)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Faltan datos requeridos (nombre o teléfono)."]);
        exit;
    }

    $destinatario = "sistemas@peruxpressinmobiliaria.com";
    $asunto = "Nuevo Lead Landing Page: $nombre";

    $mensaje = "Has recibido un nuevo contacto desde la landing page.\n\n";
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
