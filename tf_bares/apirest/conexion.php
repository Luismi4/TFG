<?php

// Establece los parámetros de conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = ""; // Aquí debes colocar la contraseña de tu base de datos si la tienes
$database = "bares";

// Crea una nueva instancia para conectarse a la BD bares
$mysql = new mysqli($servername, $username, $password, $database);

// Verifica si hay errores en la conexión
if ($mysql->connect_error) {
    die("Error de conexión: " . $mysql->connect_error);
}

// Configura la conexión para usar caracteres UTF-8
$mysql->set_charset("utf8");