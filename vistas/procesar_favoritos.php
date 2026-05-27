<?php
require_once '../conexionConfig.php';
require_once '../configuracion_estilos.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'Visitante') {
    header("Location: ../index.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$negocio_id = (int)$_GET['id'];

// 1. Obtener la categoría del negocio para saber a qué sección redirigir y mantener el scroll
$stmt_cat = $pdo->prepare("SELECT categoria FROM negocios WHERE id = ?");
$stmt_cat->execute([$negocio_id]);
$negocio = $stmt_cat->fetch(PDO::FETCH_ASSOC);

// Mapeo limpio de IDs de sección
$ancla = "tendencias";
if ($negocio) {
    $categoria = $negocio['categoria'];
    if ($categoria === 'Artesanías') $ancla = "artesanias";
    elseif ($categoria === 'Gastronomía') $ancla = "gastronomia";
    elseif ($categoria === 'Servicios') $ancla = "servicios";
}

// 2. Ejecutar el Toggle de Favoritos
$stmt = $pdo->prepare("SELECT id FROM favoritos WHERE usuario_id = ? AND negocio_id = ?");
$stmt->execute([$usuario_id, $negocio_id]);

if ($stmt->fetch()) {
    $del = $pdo->prepare("DELETE FROM favoritos WHERE usuario_id = ? AND negocio_id = ?");
    $del->execute([$usuario_id, $negocio_id]);
} else {
    $ins = $pdo->prepare("INSERT INTO favoritos (usuario_id, negocio_id) VALUES (?, ?)");
    $ins->execute([$usuario_id, $negocio_id]);
}

// Redirección exacta con el ancla del elemento
header("Location: ../index.php#" . $ancla);
exit;