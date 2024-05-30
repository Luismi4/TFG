<?php
require_once 'conexion.php';

header('Content-Type: application/json; charset=utf-8');

$response = array();

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET['nombre'])) {
        $nombre = $_GET['nombre'];

        // Validar entrada para evitar inyecciones SQL
        $nombre = mysqli_real_escape_string($mysql, $nombre);

        $consulta = "SELECT * FROM bares_restaurantes WHERE BINARY Nombre_bar = ?";
        $stmt = $mysql->prepare($consulta);

        if (!$stmt) {
            http_response_code(500);
            echo json_encode(array('mensaje' => 'Error al preparar la consulta.'));
            exit;
        }

        $stmt->bind_param("s", $nombre);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $response['bares'] = array();
            
            while ($fila = $result->fetch_assoc()) {
                $idBar = $fila['Id_bar'];
                $nombreBar = $fila['Nombre_bar'];
                $webBar = $fila['Web_bar'];
                $direccionBar = $fila['Direccion_bar'];
                $provinciaBar = $fila['Provincia'];
        
                $consulta_mcartas = "SELECT * FROM mcartas WHERE MC_IdB = ?";
                $stmt_mcartas = $mysql->prepare($consulta_mcartas);
        
                if (!$stmt_mcartas) {
                    http_response_code(500);
                    echo json_encode(array('mensaje' => 'Error al preparar la consulta de cartas.'));
                    exit;
                }
        
                $stmt_mcartas->bind_param("i", $idBar);
                $stmt_mcartas->execute();
                $result_mcartas = $stmt_mcartas->get_result();
        
                $mcartas = array();
                while ($fila_mcartas = $result_mcartas->fetch_assoc()) {
                    $mcarta = array(
                        'MC_idC' => $fila_mcartas['MC_IdC'],
                        'MC_IdB' => $fila_mcartas['MC_IdB'],
                        'MC_plato1' => $fila_mcartas['MC_plato1'],
                        'MC_plato2' => $fila_mcartas['MC_plato2'],
                        'MC_bebida1' => $fila_mcartas['MC_bebida1'],
                        'MC_bebida2' => $fila_mcartas['MC_bebida2'],
                        'MC_preciop1' => $fila_mcartas['MC_preciop1'],
                        'MC_preciop2' => $fila_mcartas['MC_preciop2'],
                        'MC_preciob1' => $fila_mcartas['MC_preciob1'],
                        'MC_preciob2' => $fila_mcartas['MC_preciob2'],
                        'MCalergias' => $fila_mcartas['MCalergias'],
                        'MCobservaciones' => $fila_mcartas['MCobservaciones']
                    );
        
                    $mcartas[] = $mcarta;
                }
        
                $datos_bar = array(
                    'Id_bar' => $idBar,
                    'Nombre_bar' => $nombreBar,
                    'Web_bar' => $webBar,
                    'Direccion_bar' => $direccionBar,
                    'Provincia' => $provinciaBar,
                    'mcartas' => $mcartas
                );
        
                $response['bares'][] = $datos_bar;
            }
        
            echo json_encode($response);
        } else {
            // Si no se encontró ningún bar, devolver un mensaje de error
            http_response_code(404);
            echo json_encode(array('mensaje' => 'Este local no está disponible'));
        }
    } else {
        // Si no se proporcionó el parámetro 'nombre', devolver un mensaje de error
        http_response_code(400);
        echo json_encode(array('mensaje' => 'Por favor, proporcione el parámetro Nombre'));
    }
} else {
    // Si el método de solicitud no es GET, devolver un mensaje de error
    http_response_code(405);
    echo json_encode(array('mensaje' => 'Método no permitido'));
};
?>