<?php


require_once __DIR__ . '/db.php';

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

echo "<h1>Conexión exitosa a la base de datos sistema_stock</h1>";
