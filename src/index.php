<?php
$host = "db";
$user = "admin";
$pass = "tienda";
$db = "sistema_stock";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

echo "<h1>Conexión exitosa a la base de datos sistema_stock</h1>";
?>
