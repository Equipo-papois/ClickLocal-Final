<?php
require_once dirname(__DIR__) . '/conexionConfig.php';
require_once dirname(__DIR__) . '/configuracion_estilos.php';

// Si no hay sesion activa, se protege el archivo de intrusos
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_rol'])) {
    header("Location: /clicklocal/index.php");
    exit;
}

$rol_actual = $_SESSION['usuario_rol'];
$id_usuario = (int)$_SESSION['usuario_id'];

// Validamos que se envien los datos correspondientes por la URL
if (!isset($_GET['id']) || !isset($_GET['accion'])) {
    header("Location: /clicklocal/index.php");
    exit;
}

$negocio_id = (int)$_GET['id'];
$accion     = $_GET['accion']; 

// Validar existencia real del negocio en la base de datos
$stmt = $pdo->prepare("SELECT * FROM negocios WHERE id = ?");
$stmt->execute([$negocio_id]);
$negocio = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$negocio) {
    header("Location: /clicklocal/index.php?error=negocio_no_existe");
    exit;
}

$id_dueno_negocio = (int)$negocio['usuario_id'];


if ($rol_actual === 'Administrador') {
    if ($accion === 'Baja') {
        $del = $pdo->prepare("DELETE FROM negocios WHERE id = ?");
        $del->execute([$negocio_id]);
        header("Location: /clicklocal/index.php?status=baja_directa_admin");
        exit;
    } elseif ($accion === 'Modificacion') {
        header("Location: /clicklocal/vistas/editar_negocio.php?id=" . $negocio_id);
        exit;
    }
}

if ($rol_actual === 'Dueño' && $id_dueno_negocio === $id_usuario) {
    if ($accion === 'Baja') {
        $ins_solicitud = $pdo->prepare("INSERT INTO solicitudes (usuario_id, negocio_id, tipo_solicitud, estado) VALUES (?, ?, 'Baja', 'Pendiente')");
        $ins_solicitud->execute([$id_usuario, $negocio_id]);
        header("Location: /clicklocal/index.php?status=solicitud_baja_enviada");
        exit;
    } elseif ($accion === 'Modificacion') {
        header("Location: /clicklocal/vistas/editar_negocio.php?id=" . $negocio_id);
        exit;
    }
}

// Si los IDs no coinciden o no tiene permisos de edicion, lo expulsa por seguridad
header("Location: /clicklocal/index.php?error=sin_permisos_sobre_comercio");
exit;