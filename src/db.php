<?php
// src/db.php
$config = require __DIR__ . '/config.php';
$conn = new mysqli(
    $config['host'],
    $config['user'],
    $config['pass'],
    $config['db']
);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
