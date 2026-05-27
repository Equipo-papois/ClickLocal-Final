<?php 
require_once '../conexionConfig.php';
require_once '../configuracion_estilos.php';

// Filtro de seguridad: Solo usuarios logueados pueden registrar un negocio
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$rol_actual = $_SESSION['usuario_rol'];
$id_usuario = $_SESSION['usuario_id'];

// PROCESAMIENTO DEL FORMULARIO AL HACER POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_negocio = trim($_POST['nombre']);
    $categoria      = $_POST['categoria'];
    $desc_corta     = trim($_POST['descripcion_corta']);
    $desc_completa  = trim($_POST['descripcion_completa']);
    $horario        = trim($_POST['horario']);
    $whatsapp       = trim($_POST['whatsapp']);
    $tipo_ubica     = $_POST['tipo_ubicacion'];
    $direccion      = isset($_POST['direccion']) ? trim($_POST['direccion']) : '';
    $facebook       = trim($_POST['facebook']);
    $instagram      = trim($_POST['instagram']);

    // Procesamiento de la imagen subida
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $ruta_temporal = $_FILES['foto']['tmp_name'];
        $dimensiones = getimagesize($ruta_temporal);
        
        // Validacion de respaldo en el servidor (Ancho >= 800 y Alto >= 600)
        if ($dimensiones && $dimensiones[0] >= 800 && $dimensiones[1] >= 600) {
            
            // Creamos la carpeta de imagenes si no existe
            $carpeta_destino = '../img/negocios/';
            if (!is_dir($carpeta_destino)) {
                mkdir($carpeta_destino, 0777, true);
            }

            // Generamos un nombre unico para evitar que se sobrescriban
            $extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $nombre_foto_unico = 'negocio_' . time() . '_' . uniqid() . '.' . $extension;
            $ruta_final_guardado = $carpeta_destino . $nombre_foto_unico;
            $url_para_base_datos = '/clicklocal/img/negocios/' . $nombre_foto_unico;

            if (move_uploaded_file($ruta_temporal, $ruta_final_guardado)) {
                
                // Empaquetamos los datos del nuevo negocio en formato JSON para la solicitud
                $datos_nuevos_array = [
                    'nombre' => $nombre_negocio,
                    'categoria' => $categoria,
                    'descripcion_corta' => $desc_corta,
                    'descripcion_completa' => $desc_completa,
                    'imagen_url' => $url_para_base_datos,
                    'horario' => $horario,
                    'whatsapp' => $whatsapp,
                    'tipo_ubicacion' => $tipo_ubica,
                    'direccion' => $direccion,
                    'facebook' => $facebook,
                    'instagram' => $instagram
                ];
                $json_datos = json_encode($datos_nuevos_array, JSON_UNESCAPED_UNICODE);

                if ($rol_actual === 'Administrador') {
                    // Si eres Admin, se inserta DIRECTO en la tabla de negocios activos
                    $stmt = $pdo->prepare("INSERT INTO negocios (usuario_id, categoria, nombre, descripcion_corta, descripcion_completa, imagen_url, horario, whatsapp, tipo_ubicacion, direccion, facebook, instagram) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$id_usuario, $categoria, $nombre_negocio, $desc_corta, $desc_completa, $url_para_base_datos, $horario, $whatsapp, $tipo_ubica, $direccion, $facebook, $instagram]);
                    
                    header("Location: ../index.php?status=alta_directa_admin");
                    exit;
                } else {
                    // PALABRA CORREGIDA A 'Pendiente' PARA HACER MATCH CON EL ENUM DE LA BASE DE DATOS
                    $stmt_sol = $pdo->prepare("INSERT INTO solicitudes (usuario_id, tipo_solicitud, datos_nuevos, estado) VALUES (?, 'Alta', ?, 'Pendiente')");
                    $stmt_sol->execute([$id_usuario, $json_datos]);
                    
                    header("Location: ../index.php?status=solicitud_alta_enviada");
                    exit;
                }
            }
        } else {
            $error_dimensiones = "La imagen no cumple con el tamaño minimo de 800x600 pixeles.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Negocio - Click Local</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-50 font-['Open_Sans'] p-4 md:p-8">

    <div class="max-w-3xl mx-auto bg-white rounded-[2.5rem] shadow-xl border border-gray-100 overflow-hidden">
        
        <div class="p-8 text-white flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4" style="background: linear-gradient(to right, <?php echo COLOR_PRINCIPAL; ?>, <?php echo COLOR_PRINCIPAL_OSCURO; ?>);">
            <div>
                <a href="../index.php" class="text-xs font-bold text-white/70 hover:text-white transition">Volver al Inicio</a>
                <h1 class="text-2xl md:text-3xl font-bold font-['Montserrat'] mt-1">Registrar mi Comercio</h1>
                <p class="text-white/80 text-xs md:text-sm mt-1">Sumate al catalogo digital de proximidad en Coatzacoalcos.</p>
            </div>
            <span class="bg-white/20 text-white text-xs font-bold px-3 py-1.5 rounded-full uppercase tracking-wider">
                Modo: <?php echo $rol_actual; ?>
            </span>
        </div>

        <form action="registro.php" method="POST" enctype="multipart/form-data" class="p-8 space-y-6">
            
            <?php if (isset($error_dimensiones)): ?>
                <div class="p-4 bg-red-50 border border-red-200 rounded-2xl text-sm font-semibold text-red-600">
                    Error: <?php echo $error_dimensiones; ?>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Nombre del Negocio *</label>
                    <input type="text" name="nombre" required placeholder="Ej. Antojitos Dona Mary"
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:border-red-800 text-sm">
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Categoría *</label>
                    <select name="categoria" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:border-red-800 text-sm bg-white">
                        <option value="Gastronomía">Gastronomía</option>
                        <option value="Artesanías">Artesanías</option>
                        <option value="Servicios">Servicios</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Descripción Corta (Máx 100 caracteres) *</label>
                <input type="text" name="descripcion_corta" maxlength="100" required placeholder="Ej. Las mejores memelas y empanadas hechas a mano con sazon istmeno."
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:border-red-800 text-sm">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Descripción Detallada *</label>
                <textarea name="descripcion_completa" rows="4" required placeholder="Cuentale a tus vecinos que vendes, especialidades, paquetes, pedidos especiales y un poco de tu historia..."
                          class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:border-red-800 text-sm"></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Horario de Atención *</label>
                    <input type="text" name="horario" required placeholder="Ej. Lunes a Sabado de 8:00 AM a 4:00 PM"
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:border-red-800 text-sm">
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Número de WhatsApp *</label>
                    <input type="tel" name="whatsapp" required placeholder="Ej. 921XXXXXXX"
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:border-red-800 text-sm font-mono">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Tipo de Entrega / Ubicación *</label>
                    <select name="tipo_ubicacion" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:border-red-800 text-sm bg-white">
                        <option value="Local Físico">Local Físico</option>
                        <option value="A domicilio">Servicio A Domicilio</option>
                        <option value="Punto de entrega público">Punto de Entrega Público</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Dirección o Punto de Referencia</label>
                    <input type="text" name="direccion" placeholder="Ej. Av. Universidad #1204, Col. Centro"
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:border-red-800 text-sm">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Enlace de Facebook (Opcional)</label>
                    <input type="url" name="facebook" placeholder="https://facebook.com/tu-pagina"
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:border-red-800 text-sm">
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Enlace de Instagram (Opcional)</label>
                    <input type="url" name="instagram" placeholder="https://instagram.com/tu-perfil"
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:border-red-800 text-sm">
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100">
                <label class="block text-sm font-bold text-gray-700 mb-2">Foto Principal del Negocio o Producto *</label>
                <div class="p-4 bg-amber-50 border border-amber-200 rounded-2xl mb-4 text-xs text-amber-800 leading-relaxed">
                    Filtro de Calidad Obligatorio: El catalogo requiere fotos nitidas en formato horizontal. La resolucion minima del archivo debe ser de 800 x 600 pixeles. Evita capturas de pantalla borrosas o pixeladas para mantener la estetica de tu tarjeta.
                </div>
                <input type="file" name="foto" id="foto_negocio" accept="image/jpeg, image/png" required 
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none cursor-pointer text-sm bg-gray-50 file:mr-4 file:py-1.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-gray-200 file:text-gray-700 hover:file:bg-gray-300">
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full py-4 text-white font-bold rounded-2xl transition shadow-lg tracking-wide text-sm"
                        style="background-color: <?php echo COLOR_PRINCIPAL; ?>;">
                    <?php echo ($rol_actual === 'Administrador') ? 'Publicar Negocio Inmediatamente' : 'Enviar Solicitud de Alta'; ?>
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
                    alert("Imagen Pixelada o Pequeña detectada (" + this.width + "x" + this.height + "px).\n\nPara mantener la calidad visual del catálogo de Click Local, por favor elije una fotografía que mide al menos 800x600 píxeles.");
                    document.getElementById('foto_negocio').value = ""; 
                }
            };
        }
    };
    </script>

</body>
</html>