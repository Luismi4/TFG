<?php
require_once 'conexion.php';  // Incluye la configuración y conexión a la base de datos

// Función para obtener y sanitizar los datos del formulario
function sanitizeFormData($input) {
    return htmlspecialchars(strip_tags($input));
}

// Establece la cabecera de contenido para JSON
header('Content-Type: application/json; charset=utf-8');

// Verificar si se recibieron datos mediante una solicitud POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener y decodificar los datos del cuerpo de la solicitud
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Verifica si los datos fueron decodificados correctamente
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(array("error" => "Datos JSON mal formateados"));
        exit;
    }
    
    if (isset($data['Usuario']) && isset($data['Contrasena'])) {
        // Obtener y sanitizar los valores de los campos de usuario
        $usuario = sanitizeFormData($data['Usuario']);
        $contrasena = sanitizeFormData($data['Contrasena']);
        
        // Consulta SQL para buscar en la base de datos si existe un registro con el Usuario proporcionado
        $sql = "SELECT MId_usuario, Mcontrasena FROM musuarios WHERE BINARY Musuario = ?";
        
        // Preparar la consulta
        if ($stmt = $mysql->prepare($sql)) {
            // Vincular los parámetros
            $stmt->bind_param("s", $usuario);

            // Ejecutar la consulta preparada
            $stmt->execute();

            // Obtener el resultado
            $resultado = $stmt->get_result();

            if ($resultado && $resultado->num_rows > 0) {
                // Si se encuentran resultados, verificamos la contraseña
                $fila = $resultado->fetch_assoc();
                $idUsuario = $fila['MId_usuario'];
                $hashed_password = $fila['Mcontrasena'];

                if (password_verify($contrasena, $hashed_password)) {
                    // Contraseña verificada correctamente
                    $response = array("idUsuario" => $idUsuario);

                    // Devuelve la respuesta en formato JSON
                    echo json_encode($response);
                } else {
                    // Contraseña incorrecta
                    echo json_encode(array("error" => "Usuario o contraseña incorrectos"));
                }
            } else {
                // Si no se encontraron resultados, devolver un mensaje de error en JSON
                echo json_encode(array("error" => "Usuario o contraseña incorrectos"));
            }

            // Cierra la declaración preparada
            $stmt->close();
        } else {
            // Si la preparación de la consulta falla, manejar el error
            echo json_encode(array("error" => "Error al preparar la consulta: " . $mysql->error));
        }
    } else {
        // Datos de usuario faltantes en la solicitud
        echo json_encode(array("error" => "Faltan datos de usuario en la solicitud"));
    }
} else {
    // Método de solicitud incorrecto
    echo json_encode(array("error" => "Método de solicitud incorrecto"));
}
?>
