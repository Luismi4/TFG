<?php
require_once 'conexion.php';

header('Content-Type: application/json; charset=utf-8');

$conn = new mysqli($servername, $username, $password, $database);

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Recibir los datos enviados desde la aplicación Android
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Procesar los datos recibidos y guardarlos en la base de datos
foreach ($data as $pedido) {
    $id_mesa = $pedido['id_mesa'];
    $fecha_pedido = $pedido['fecha_pedido'];
    $plato_1 = $pedido['plato_1'];
    $cantidad_plato_1 = $pedido['cantidad_plato_1'];
    $plato_2 = $pedido['plato_2'];
    $cantidad_plato_2 = $pedido['cantidad_plato_2'];
    $bebida_1 = $pedido['bebida_1'];
    $cantidad_bebida_1 = $pedido['cantidad_bebida_1'];
    $bebida_2 = $pedido['bebida_2'];
    $cantidad_bebida_2 = $pedido['cantidad_bebida_2'];
    // Aquí puedes continuar con los demás campos según tu estructura de base de datos

    // Insertar los datos en la tabla de pedidos
    $sql = "INSERT INTO mpedidos (id_mesa, fecha_pedido, plato_1, cantidad_plato_1, plato_2, cantidad_plato_2, bebida_1, cantidad_bebida_1, bebida_2, cantidad_bebida_2) 
            VALUES ('$id_mesa', '$fecha_pedido', '$plato_1', '$cantidad_plato_1', '$plato_2', '$cantidad_plato_2', '$bebida_1', '$cantidad_bebida_1', '$bebida_2', '$cantidad_bebida_2')";
    // Ejecutar la consulta
    if ($conn->query($sql) !== TRUE) {
        echo "Error al guardar el pedido: " . $conn->error;
    }
}

// Cerrar la conexión
$conn->close();

?>