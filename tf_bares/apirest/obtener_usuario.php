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

    // Verificar y sanitizar el idUsuario recibido
    $idUsuario = isset($data['idUsuario']) ? intval($data['idUsuario']) : null;

    // Verifica que el idUsuario no sea nulo
    if (!$idUsuario) {
        sendResponse(array("error" => "El idUsuario es obligatorio."));
    }

    // Consultar los datos del usuario basado en el idUsuario
    $sql = "SELECT Mnombre AS Nombre, Musuario AS Usuario, Mcorreo AS Correo, Mdni AS DNI, Mcontrasena AS Contrasena FROM musuarios WHERE MId_usuario = ?";
    $stmt = $mysql->prepare($sql);
    if (!$stmt) {
        sendResponse(array("error" => "Error al preparar la consulta: " . htmlspecialchars($mysql->error)));
    }
    $stmt->bind_param("i", $idUsuario);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Usuario encontrado
        $usuario = $result->fetch_assoc();

        // Desencriptar la contraseña
        $contrasena_desencriptada = decryptPassword($usuario['Contrasena']);

        // Agregar la contraseña desencriptada al array del usuario
        $usuario['Contrasena'] = $contrasena_desencriptada;

        sendResponse(array("success" => "Usuario encontrado", "usuario" => $usuario));
    } else {
        // Usuario no encontrado
        sendResponse(array("error" => "Usuario no encontrado"));
    }

    $stmt->close();
    $mysql->close();

} else {
    // Método de solicitud no válido
    sendResponse(array("error" => "Método de solicitud no válido."));
}

// Función para desencriptar la contraseña
function decryptPassword($hashed_password) {
    // Asume que usas bcrypt para encriptar la contraseña
    return password_verify('password', $hashed_password);
}
?>
