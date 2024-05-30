<?php
require_once 'conexion.php';  // Incluir la configuración y conexión a la base de datos

// Establece la cabecera de contenido para UTF-8 y JSON
header('Content-Type: application/json; charset=utf-8');

// Función para enviar la respuesta en formato JSON
function sendResponse($data) {
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Verificar el método de la solicitud
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener y decodificar los datos del cuerpo de la solicitud
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['idUsuario'])) {
        // Obtener y sanitizar los valores de los campos de usuario
        $idUsuario = filter_var($data['idUsuario'], FILTER_SANITIZE_NUMBER_INT);
        
        // Verificar que el idUsuario es válido
        if (!$idUsuario) {
            sendResponse(array("error" => "El ID del usuario es inválido."));
        }

        // Consulta SQL para eliminar el usuario de la base de datos
        $sql = "DELETE FROM musuarios WHERE MId_usuario = ?";
        
        // Preparar la consulta
        if ($stmt = $mysql->prepare($sql)) {
            // Vincular los parámetros
            $stmt->bind_param("i", $idUsuario);

            // Ejecutar la consulta preparada
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    // Si se ha eliminado algún registro
                    sendResponse(array("success" => "Usuario eliminado correctamente."));
                } else {
                    // Si no se ha encontrado ningún registro
                    sendResponse(array("error" => "Usuario no encontrado."));
                }
            } else {
                // Si la ejecución de la consulta falla
                sendResponse(array("error" => "Error al eliminar el usuario: " . htmlspecialchars($stmt->error)));
            }

            // Cierra la declaración preparada
            $stmt->close();
        } else {
            // Si la preparación de la consulta falla
            sendResponse(array("error" => "Error al preparar la consulta: " . htmlspecialchars($mysql->error)));
        }
    } else {
        // Datos faltantes en la solicitud
        sendResponse(array("error" => "Faltan datos de usuario en la solicitud."));
    }

    $mysql->close();
} else {
    // Método de solicitud no válido
    sendResponse(array("error" => "Método de solicitud no válido."));
}
?>
