<?php
require_once '../conexionConfig.php';
require_once '../configuracion_estilos.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = $_SESSION['usuario_id'];
    $nombre = trim($_POST['nombre']);
    $categoria = $_POST['categoria'];
    $descripcion_corta = trim($_POST['descripcion_corta']);
    $descripcion_completa = trim($_POST['descripcion_completa']);
    $horario = trim($_POST['horario']);
    

    $whatsapp = preg_replace('/[^0-9]/', '', $_POST['whatsapp']);
    
    // 2. Validamos que mida exactamente 10 caracteres numéricos
    if (strlen($whatsapp) !== 10) {
        die("❌ Error de Seguridad: El número de WhatsApp debe contener exactamente 10 dígitos numéricos.");
    }

    $tipo_ubicacion = $_POST['tipo_ubicacion'];
    $direccion = ($tipo_ubicacion === 'Local Físico') ? trim($_POST['direccion']) : null;
    $facebook = !empty($_POST['facebook']) ? trim($_POST['facebook']) : null;
    $instagram = !empty($_POST['instagram']) ? trim($_POST['instagram']) : null;

    if (empty($nombre) || empty($categoria) || empty($descripcion_corta) || empty($descripcion_completa) || empty($horario)) {
        die("❌ Error: Llena todos los datos obligatorios.");
    }

    // Subida segura de imágenes
    $imagen_url = "https://images.unsplash.com/photo-1607975218250-7faaf3e36bbf";
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $carpeta_subidas = '../subidas/';
        if (!is_dir($carpeta_subidas)) {
            mkdir($carpeta_subidas, 0777, true);
        }
        $nombre_foto = time() . "_" . basename($_FILES['foto']['name']);
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $carpeta_subidas . $nombre_foto)) {
            $imagen_url = "subidas/" . $nombre_foto;
        }
    }


    $datos_negocio = json_encode([
        'categoria' => $categoria,
        'nombre' => $nombre,
        'descripcion_corta' => $descripcion_corta,
        'descripcion_completa' => $descripcion_completa,
        'imagen_url' => $imagen_url,
        'horario' => $horario,
        'whatsapp' => $whatsapp,
        'tipo_ubicacion' => $tipo_ubicacion,
        'direccion' => $direccion,
        'facebook' => $facebook,
        'instagram' => $instagram
    ], JSON_UNESCAPED_UNICODE);

    $sql = "INSERT INTO solicitudes (usuario_id, tipo_solicitud, datos_nuevos, estado) VALUES (?, 'Alta', ?, 'Pendiente')";
    $stmt = $pdo->prepare($sql);
    
    try {
        $stmt->execute([$usuario_id, $datos_negocio]);
        header("Location: ../index.php?solicitud=alta");
        exit;
    } catch (PDOException $e) {
        die("Error al procesar la solicitud: " . $e->getMessage());
    }
}
?>