<?php
require_once 'conexion.php';

// Establece la cabecera de contenido para UTF-8 y JSON
header('Content-Type: application/json; charset=utf-8');

// Función para enviar la respuesta en formato JSON
function sendResponse($data) {
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Verificar el método de la solicitud
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Obtener los datos del cuerpo de la solicitud
    $data = json_decode(file_get_contents('php://input'), true);

    // Verificar y sanitizar los valores recibidos
    $nombre = isset($data['Nombre']) ? htmlspecialchars(strip_tags($data['Nombre'])) : null;
    $usuario = isset($data['Usuario']) ? htmlspecialchars(strip_tags($data['Usuario'])) : null;
    $correo = isset($data['Correo']) ? filter_var($data['Correo'], FILTER_VALIDATE_EMAIL) : null;
    $dni = isset($data['DNI']) ? htmlspecialchars(strip_tags($data['DNI'])) : null;
    $contrasena = isset($data['Contrasena']) ? htmlspecialchars(strip_tags($data['Contrasena'])) : null;

    // Verifica los valores recibidos (para depuración)
    if (!$nombre || !$usuario || !$correo || !$dni || !$contrasena) {
        sendResponse(array(
            "error" => "Todos los campos son obligatorios y deben ser válidos.",
            "recibido" => array(
                "Nombre" => $nombre,
                "Usuario" => $usuario,
                "Correo" => $correo,
                "DNI" => $dni,
                "Contrasena" => $contrasena
            )
        ));
    }

    // Verificar la longitud del DNI
    if (strlen($dni) != 9) {
        sendResponse(array("error" => "El DNI debe tener 9 caracteres."));
    }

    // Verificar la fortaleza de la contraseña
    if (strlen($contrasena) < 8) {
        sendResponse(array("error" => "La contraseña debe tener al menos 8 caracteres."));
    }

    // Consultar si el usuario ya está registrado por nombre de usuario o DNI
    $sql = "SELECT * FROM musuarios WHERE BINARY Musuario = ? OR Mdni = ?";
    $stmt = $mysql->prepare($sql);
    if (!$stmt) {
        sendResponse(array("error" => "Error al preparar la consulta: " . htmlspecialchars($mysql->error)));
    }
    $stmt->bind_param("ss", $usuario, $dni);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Usuario ya está registrado
        sendResponse(array("error" => "El nombre de usuario o DNI ya están en uso en la aplicación"));
    } else {
        // Insertar nuevo usuario en la base de datos
        $hashed_password = password_hash($contrasena, PASSWORD_DEFAULT); // Hashing de la contraseña
        $sql_insert = "INSERT INTO musuarios (Mnombre, Musuario, Mcorreo, Mdni, Mcontrasena) VALUES (?, ?, ?, ?, ?)";
        $stmt_insert = $mysql->prepare($sql_insert);
        if (!$stmt_insert) {
            sendResponse(array("error" => "Error al preparar la consulta de inserción: " . htmlspecialchars($mysql->error)));
        }
        $stmt_insert->bind_param("sssss", $nombre, $usuario, $correo, $dni, $hashed_password);
        
        if ($stmt_insert->execute()) {
            sendResponse(array("success" => "Usuario registrado correctamente en la aplicación"));
        } else {
            sendResponse(array("error" => "Error al registrar al usuario: " . htmlspecialchars($mysql->error)));
        }
        $stmt_insert->close();
    }

    $stmt->close();
    $mysql->close();

} else {
    // Método de solicitud no válido
    sendResponse(array("error" => "Método de solicitud no válido."));
}
?>
