<?php
require_once '../conexionConfig.php';

// Arrancamos el manejo de sesiones de forma obligatoria antes de validar
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Filtro estricto de seguridad para el procesador: Solo administradores reales
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'Administrador') {
    header("Location: ../index.php");
    exit;
}

if (!isset($_GET['id']) || !isset($_GET['accion'])) {
    header("Location: admin_solicitudes.php");
    exit;
}

$solicitud_id = (int)$_GET['id'];
$accion_admin = $_GET['accion']; 

// 1. Obtener la solicitud pendiente
$stmt = $pdo->prepare("SELECT * FROM solicitudes WHERE id = ?");
$stmt->execute([$solicitud_id]);
$solicitud = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$solicitud) {
    header("Location: admin_solicitudes.php?error=solicitud_no_encontrada");
    exit;
}

// Decodificar el paquete JSON enviado por el solicitante
$datos = json_decode($solicitud['datos_nuevos'], true);
$id_del_dueno = $solicitud['usuario_id'];

if ($accion_admin === 'Aprobar') {
    
    // CASO A: ES UN ALTA NUEVA (Se inserta por primera vez en la tabla negocios)
    if ($solicitud['tipo_solicitud'] === 'Alta') {
        $stmt_insert = $pdo->prepare("INSERT INTO negocios (usuario_id, categoria, nombre, descripcion_corta, descripcion_completa, imagen_url, horario, whatsapp, tipo_ubicacion, direccion, facebook, instagram) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt_insert->execute([
            $id_del_dueno,
            $datos['categoria'],
            $datos['nombre'],
            $datos['descripcion_corta'],
            $datos['descripcion_completa'],
            $datos['imagen_url'],
            $datos['horario'],
            $datos['whatsapp'],
            $datos['tipo_ubicacion'],
            $datos['direccion'],
            $datos['facebook'],
            $datos['instagram']
        ]);

        // Ascendemos el rol del usuario a 'Dueño' de manera automática al aprobar su primer comercio
        $stmt_rol = $pdo->prepare("UPDATE usuarios SET rol = 'Dueño' WHERE id = ? AND rol = 'Visitante'");
        $stmt_rol->execute([$id_del_dueno]);
    } 
    
    // CASO B: ES UNA MODIFICACIÓN DE UN NEGOCIO EXISTENTE
    elseif ($solicitud['tipo_solicitud'] === 'Modificacion') {
        $stmt_update = $pdo->prepare("UPDATE negocios SET categoria = ?, nombre = ?, descripcion_corta = ?, descripcion_completa = ?, horario = ?, whatsapp = ?, tipo_ubicacion = ?, direccion = ?, facebook = ?, instagram = ? WHERE id = ?");
        
        $stmt_update->execute([
            $datos['categoria'],
            $datos['nombre'],
            $datos['descripcion_corta'],
            $datos['descripcion_completa'],
            $datos['horario'],
            $datos['whatsapp'],
            $datos['tipo_ubicacion'],
            $datos['direccion'],
            $datos['facebook'],
            $datos['instagram'],
            $solicitud['negocio_id']
        ]);
    } 
    
    // CASO C: ES UNA SOLICITUD DE BAJA
    elseif ($solicitud['tipo_solicitud'] === 'Baja') {
        $stmt_delete = $pdo->prepare("DELETE FROM negocios WHERE id = ?");
        $stmt_delete->execute([$solicitud['negocio_id']]);
    }

    // Actualizamos el estado de la solicitud en la bitácora
    $stmt_close = $pdo->prepare("UPDATE solicitudes SET estado = 'Aprobada' WHERE id = ?");
    $stmt_close->execute([$solicitud_id]);

    header("Location: admin_solicitudes.php?status=solicitud_aprobada_con_exito");
    exit;

} else {
    // SI EL ADMINISTRADOR RECHAZA LA SOLICITUD
    $stmt_close = $pdo->prepare("UPDATE solicitudes SET estado = 'Rechazada' WHERE id = ?");
    $stmt_close->execute([$solicitud_id]);

    header("Location: admin_solicitudes.php?status=solicitud_rechazada");
    exit;
}