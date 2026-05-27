<?php
require_once '../conexionConfig.php';
require_once '../configuracion_estilos.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'Visitante') {
    header("Location: ../index.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$negocio_id = (int)$_GET['negocio_id'];
$voto       = (int)$_GET['voto'];

// 1. Obtener la categoría para mantener la posición del scroll
$stmt_cat = $pdo->prepare("SELECT categoria FROM negocios WHERE id = ?");
$stmt_cat->execute([$negocio_id]);
$negocio = $stmt_cat->fetch(PDO::FETCH_ASSOC);

$ancla = "tendencias";
if ($negocio) {
    $categoria = $negocio['categoria'];
    if ($categoria === 'Artesanías') $ancla = "artesanias";
    elseif ($categoria === 'Gastronomía') $ancla = "gastronomia";
    elseif ($categoria === 'Servicios') $ancla = "servicios";
}

// 2. Registrar el voto
if ($voto >= 1 && $voto <= 5) {
    $stmt = $pdo->prepare("
        INSERT INTO calificaciones (usuario_id, negocio_id, estrellas) 
        VALUES (?, ?, ?) 
        ON DUPLICATE KEY UPDATE estrellas = VALUES(estrellas)
    ");
    $stmt->execute([$usuario_id, $negocio_id, $voto]);
}

// Redirección exacta con el ancla del elemento
header("Location: ../index.php#" . $ancla);
exit;