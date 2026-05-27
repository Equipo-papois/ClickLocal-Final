<?php
$host = "localhost";
$db   = "click_local";
$user = "root";
$pass = ""; // Vacío por defecto en XAMPP

try {
    // IMPORTANTE: Que se llame exactamente $pdo y en minúsculas
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>