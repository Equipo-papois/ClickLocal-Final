<?php 
require_once '../conexionConfig.php';
require_once '../configuracion_estilos.php';

if (!isset($_SESSION['usuario_id']) || !isset($_GET['id'])) {
    header("Location: ../index.php");
    exit;
}

$id_usuario = $_SESSION['usuario_id'];
$rol_actual = $_SESSION['usuario_rol'];
$negocio_id = (int)$_GET['id'];

// Obtener los datos actuales del negocio
$stmt = $pdo->prepare("SELECT * FROM negocios WHERE id = ?");
$stmt->execute([$negocio_id]);
$negocio = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$negocio) {
    header("Location: ../index.php");
    exit;
}

// Validar que el usuario sea Admin o el dueño legítimo de este negocio
if ($rol_actual !== 'Administrador' && ($rol_actual !== 'Dueño' || $negocio['usuario_id'] != $id_usuario)) {
    header("Location: ../index.php");
    exit;
}

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $categoria = $_POST['categoria'];
    $desc_corta = trim($_POST['descripcion_corta']);
    $desc_completa = trim($_POST['descripcion_completa']);
    $horario = trim($_POST['horario']);
    $whatsapp = trim($_POST['whatsapp']);
    $tipo_ubica = $_POST['tipo_ubicacion'];
    $direccion = trim($_POST['direccion']);
    $facebook = trim($_POST['facebook']);
    $instagram = trim($_POST['instagram']);
    
    // Mantener la foto anterior por defecto
    $url_imagen_final = $negocio['imagen_url'];

    // Si sube una nueva foto, validarla y reemplazar
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERRORS_OK) {
        $ruta_temporal = $_FILES['foto']['tmp_name'];
        $dimensiones = getimagesize($ruta_temporal);
        
        if ($dimensiones && $dimensiones[0] >= 800 && $dimensiones[1] >= 600) {
            $extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $nombre_foto = 'negocio_upd_' . time() . '_' . uniqid() . '.' . $extension;
            $ruta_guardado = '../img/negocios/' . $nombre_foto;
            
            if (move_uploaded_file($ruta_temporal, $ruta_guardado)) {
                $url_imagen_final = '/clicklocal/img/negocios/' . $nombre_foto;
            }
        }
    }

    $datos_actualizados = [
        'nombre' => $nombre,
        'categoria' => $categoria,
        'descripcion_corta' => $desc_corta,
        'descripcion_completa' => $desc_completa,
        'imagen_url' => $url_imagen_final,
        'horario' => $horario,
        'whatsapp' => $whatsapp,
        'tipo_ubicacion' => $tipo_ubica,
        'direccion' => $direccion,
        'facebook' => $facebook,
        'instagram' => $instagram
    ];

    if ($rol_actual === 'Administrador') {
        // Actualización directa a la base de datos
        $upd = $pdo->prepare("UPDATE negocios SET categoria=?, nombre=?, descripcion_corta=?, descripcion_completa=?, imagen_url=?, horario=?, whatsapp=?, tipo_ubicacion=?, direccion=?, facebook=?, instagram=? WHERE id=?");
        $upd->execute([$categoria, $nombre, $desc_corta, $desc_completa, $url_imagen_final, $horario, $whatsapp, $tipo_ubica, $direccion, $facebook, $instagram, $negocio_id]);
        header("Location: ../index.php?status=modificacion_directa");
        exit;
    } else {
        // El dueño genera una solicitud
        $json_datos = json_encode($datos_actualizados, JSON_UNESCAPED_UNICODE);
        $ins_solicitud = $pdo->prepare("INSERT INTO solicitudes (usuario_id, negocio_id, tipo_solicitud, datos_nuevos, estado) VALUES (?, ?, 'Modificacion', ?, 'Pendiente')");
        $ins_solicitud->execute([$id_usuario, $negocio_id, $json_datos]);
        header("Location: ../index.php?status=solicitud_modificacion_enviada");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Negocio - Click Local</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-50 font-['Open_Sans'] p-4 md:p-8">

    <div class="max-w-3xl mx-auto bg-white rounded-[2.5rem] shadow-xl border border-gray-100 overflow-hidden">
        
        <div class="p-8 text-white flex justify-between items-center" style="background-color: <?php echo COLOR_PRINCIPAL; ?>;">
            <div>
                <a href="../index.php" class="text-xs font-bold text-white/70 hover:text-white transition">Volver al Inicio</a>
                <h1 class="text-2xl font-bold font-['Montserrat'] mt-1">Modificar Datos del Negocio</h1>
            </div>
            <span class="bg-white/20 text-white text-xs font-bold px-3 py-1.5 rounded-full uppercase tracking-wider">
                Modo: <?php echo $rol_actual; ?>
            </span>
        </div>

        <form action="editar_negocio.php?id=<?php echo $negocio_id; ?>" method="POST" enctype="multipart/form-data" class="p-8 space-y-6">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Nombre del Negocio</label>
                    <input type="text" name="nombre" value="<?php echo htmlspecialchars($negocio['nombre']); ?>" required class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm">
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Categoría</label>
                    <select name="categoria" required class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm bg-white">
                        <option value="Gastronomía" <?php if($negocio['categoria']=='Gastronomía') echo 'selected'; ?>>Gastronomía</option>
                        <option value="Artesanías" <?php if($negocio['categoria']=='Artesanías') echo 'selected'; ?>>Artesanías</option>
                        <option value="Servicios" <?php if($negocio['categoria']=='Servicios') echo 'selected'; ?>>Servicios</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Descripción Corta</label>
                <input type="text" name="descripcion_corta" value="<?php echo htmlspecialchars($negocio['descripcion_corta']); ?>" maxlength="100" required class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Descripción Detallada</label>
                <textarea name="descripcion_completa" rows="4" required class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm"><?php echo htmlspecialchars($negocio['descripcion_completa']); ?></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Horario de Atención</label>
                    <input type="text" name="horario" value="<?php echo htmlspecialchars($negocio['horario']); ?>" required class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm">
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Número de WhatsApp</label>
                    <input type="tel" name="whatsapp" value="<?php echo htmlspecialchars($negocio['whatsapp']); ?>" required class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm font-mono">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Tipo de Ubicación</label>
                    <select name="tipo_ubicacion" required class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm bg-white">
                        <option value="Local Físico" <?php if($negocio['tipo_ubicacion']=='Local Físico') echo 'selected'; ?>>Local Físico</option>
                        <option value="A domicilio" <?php if($negocio['tipo_ubicacion']=='A domicilio') echo 'selected'; ?>>Servicio A Domicilio</option>
                        <option value="Punto de entrega público" <?php if($negocio['tipo_ubicacion']=='Punto de entrega público') echo 'selected'; ?>>Punto de Entrega Público</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Dirección o Referencia</label>
                    <input type="text" name="direccion" value="<?php echo htmlspecialchars($negocio['direccion']); ?>" class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Enlace de Facebook</label>
                    <input type="url" name="facebook" value="<?php echo htmlspecialchars($negocio['facebook']); ?>" class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm">
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Enlace de Instagram</label>
                    <input type="url" name="instagram" value="<?php echo htmlspecialchars($negocio['instagram']); ?>" class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm">
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100">
                <label class="block text-sm font-bold text-gray-700 mb-2">Reemplazar Foto (Opcional)</label>
                <div class="mb-3 text-xs text-gray-500">Si no seleccionas un archivo nuevo, se conservará la imagen actual. Recuerda que debe medir al menos 800x600 px.</div>
                <input type="file" name="foto" id="foto_negocio" accept="image/jpeg, image/png" class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm bg-gray-50">
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full py-4 text-white font-bold rounded-2xl transition shadow-lg tracking-wide text-sm" style="background-color: <?php echo COLOR_PRINCIPAL; ?>;">
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>

    <script>
    document.getElementById('foto_negocio').onchange = function() {
        const archivo = this.files[0];
        if (archivo) {
            const imagenObjeto = new Image();
            imagenObjeto.src = URL.createObjectURL(archivo);
            imagenObjeto.onload = function() {
                if (this.width < 800 || this.height < 600) {
                    alert("La imagen es muy pequeña (" + this.width + "x" + this.height + "px). Sube una foto de al menos 800x600 píxeles.");
                    document.getElementById('foto_negocio').value = "";
                }
            };
        }
    };
    </script>
</body>
</html>