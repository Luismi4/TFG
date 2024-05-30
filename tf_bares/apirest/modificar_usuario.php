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
    // Debugging: Ver datos recibidos
    $inputData = file_get_contents("php://input");
    $postData = json_decode($inputData, true);
    error_log("Datos recibidos: " . print_r($postData, true));

    $idUsuario = filter_var($postData['idUsuario'], FILTER_SANITIZE_NUMBER_INT);

    // Verifica si se proporcionó el ID del usuario
    if (!$idUsuario) {
        sendResponse(array("error" => "El ID de usuario es obligatorio."));
    }

    // Consultar si el usuario existe
    $sql = "SELECT * FROM musuarios WHERE MId_usuario = ?";
    $stmt = $mysql->prepare($sql);
    if (!$stmt) {
        sendResponse(array("error" => "Error al preparar la consulta: " . htmlspecialchars($mysql->error)));
    }
    $stmt->bind_param("i", $idUsuario);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // Usuario no encontrado
        sendResponse(array("error" => "El usuario con ID $idUsuario no existe en la base de datos."));
    } else {
        // Obtener los datos del usuario
        $usuario = $result->fetch_assoc();

        // Actualizar los datos del usuario
        $nombre = filter_var($postData['Nombre'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $correo = filter_var($postData['Correo'], FILTER_VALIDATE_EMAIL);
        $dni = filter_var($postData['DNI'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $contrasena = filter_var($postData['Contrasena'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        // Verificar si se proporcionaron los datos actualizados
        if (!$nombre || !$correo || !$dni || !$contrasena) {
            sendResponse(array("error" => "Todos los campos son obligatorios.", "usuario" => $usuario));
        }

        // Verificar la longitud del DNI
        if (strlen($dni) != 9) {
            sendResponse(array("error" => "El DNI debe tener 9 caracteres.", "usuario" => $usuario));
        }

        // Verificar la fortaleza de la contraseña
        if (strlen($contrasena) < 8) {
            sendResponse(array("error" => "La contraseña debe tener al menos 8 caracteres.", "usuario" => $usuario));
        }

        $hashed_password = password_hash($contrasena, PASSWORD_DEFAULT); // Hashing de la contraseña
        
        $sql_update = "UPDATE musuarios SET Mnombre = ?, Mcorreo = ?, Mdni = ?, Mcontrasena = ? WHERE MId_usuario = ?";
        $stmt_update = $mysql->prepare($sql_update);
        if (!$stmt_update) {
            sendResponse(array("error" => "Error al preparar la consulta de actualización: " . htmlspecialchars($mysql->error), "usuario" => $usuario));
        }
        $stmt_update->bind_param("ssssi", $nombre, $correo, $dni, $hashed_password, $idUsuario);
        
        if ($stmt_update->execute()) {
            // Éxito al actualizar los datos del usuario
            // Envía los datos del usuario actualizados como respuesta
            $usuario['nombre'] = $nombre;
            $usuario['correo'] = $correo;
            $usuario['dni'] = $dni;
            
            sendResponse(array("success" => "Datos del usuario actualizados correctamente", "usuario" => $usuario));
        } else {
            sendResponse(array("error" => "Error al actualizar los datos del usuario: " . htmlspecialchars($stmt_update->error), "usuario" => $usuario));
        }
        $stmt_update->close();
    }

    $stmt->close();
    $mysql->close();

} else {
    // Método de solicitud no válido
    sendResponse(array("error" => "Método de solicitud no válido."));
}
?>
