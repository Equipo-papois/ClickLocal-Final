<?php
require_once '../conexionConfig.php';
require_once '../configuracion_estilos.php';

// Filtro estricto de seguridad: Solo Administradores
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'Administrador') {
    header("Location: ../index.php");
    exit;
}

// Traer todas las solicitudes pendientes junto con el nombre del usuario
$stmt = $pdo->query("SELECT s.*, u.nombre AS usuario_nombre FROM solicitudes s JOIN usuarios u ON s.usuario_id = u.id WHERE s.estado = 'Pendiente' ORDER BY s.creado_en DESC");
$solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Solicitudes - Click Local</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-50 font-['Open_Sans'] p-6">

    <div class="max-w-5xl mx-auto">
        <div class="mb-6 flex justify-between items-center">
            <div>
                <a href="../index.php" class="text-xs font-bold text-gray-500 hover:text-red-800 transition">Volver al Inicio</a>
                <h1 class="text-3xl font-bold text-gray-800 font-['Montserrat'] mt-1">Solicitudes Pendientes</h1>
            </div>
            <span class="bg-red-100 text-red-800 text-xs font-bold px-3 py-1.5 rounded-full uppercase">Modo: Administrador</span>
        </div>

        <?php if (isset($_GET['status'])): ?>
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-2xl text-sm font-semibold text-green-700">
                Sistema: La solicitud ha sido procesada e impactada en la base de datos de manera exitosa.
            </div>
        <?php endif; ?>

        <?php if (count($solicitudes) === 0): ?>
            <div class="bg-white p-8 rounded-3xl border border-gray-100 text-center text-gray-500 shadow-sm">
                No hay movimientos ni solicitudes pendientes de revisión en este momento.
            </div>
        <?php else: ?>
            <div class="space-y-6">
                <?php foreach ($solicitudes as $sol): 
                    $datos = json_decode($sol['datos_nuevos'], true);
                    $nombre_negocio_afectado = isset($datos['nombre']) ? $datos['nombre'] : 'Negocio ID: ' . $sol['negocio_id'];
                ?>
                    <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex flex-col gap-6">
                        
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 pb-4 border-b border-gray-100">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold px-2.5 py-1 rounded-full <?php 
                                        echo $sol['tipo_solicitud'] === 'Alta' ? 'bg-green-100 text-green-800 border border-green-200' : 
                                            ($sol['tipo_solicitud'] === 'Modificacion' ? 'bg-blue-100 text-blue-800 border border-blue-200' : 'bg-red-100 text-red-800 border border-red-200'); 
                                    ?>">
                                        Solicitud de <?php echo $sol['tipo_solicitud']; ?>
                                    </span>
                                    <span class="text-xs text-gray-400">Enviada por: <strong><?php echo htmlspecialchars($sol['usuario_nombre']); ?></strong></span>
                                </div>
                                <h2 class="text-2xl font-bold text-gray-800 mt-2"><?php echo htmlspecialchars($nombre_negocio_afectado); ?></h2>
                                <p class="text-xs text-gray-400 mt-1">Fecha de envío: <?php echo $sol['creado_en']; ?></p>
                            </div>

                            <div class="flex gap-2 w-full sm:w-auto">
                                <a href="procesar_solicitud.php?id=<?php echo $sol['id']; ?>&accion=Aprobar" 
                                   class="text-center bg-green-700 hover:bg-green-800 text-white px-5 py-2.5 rounded-xl text-xs font-bold transition shadow-sm">
                                    Aprobar Cambio
                                </a>
                                <a href="procesar_solicitud.php?id=<?php echo $sol['id']; ?>&accion=Rechazar" 
                                   onclick="return confirm('¿Seguro que deseas rechazar este movimiento?');"
                                   class="text-center bg-gray-100 hover:bg-gray-200 text-gray-600 px-5 py-2.5 rounded-xl text-xs font-bold transition">
                                    Rechazar
                                </a>
                            </div>
                        </div>

                        <?php if (!empty($datos)): ?>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-gray-50 p-6 rounded-2xl border border-gray-100">
                                
                                <div class="md:col-span-1">
                                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Imagen Propuesta</p>
                                    <div class="w-full h-40 bg-gray-200 rounded-xl overflow-hidden border border-gray-200">
                                        <img src="<?php echo htmlspecialchars($datos['imagen_url']); ?>" class="w-full h-full object-cover" alt="Propuesta">
                                    </div>
                                </div>

                                <div class="md:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <p class="text-xs font-bold text-gray-400 uppercase">Categoría</p>
                                        <p class="text-gray-800 font-semibold mt-0.5"><?php echo htmlspecialchars($datos['categoria']); ?></p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-gray-400 uppercase">Horario de Atención</p>
                                        <p class="text-gray-800 font-semibold mt-0.5"><?php echo htmlspecialchars($datos['horario']); ?></p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-gray-400 uppercase">Número de WhatsApp</p>
                                        <p class="text-gray-800 font-semibold mt-0.5 font-mono"><?php echo htmlspecialchars($datos['whatsapp']); ?></p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-gray-400 uppercase">Tipo de Ubicación</p>
                                        <p class="text-gray-800 font-semibold mt-0.5"><?php echo htmlspecialchars($datos['tipo_ubicacion']); ?></p>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <p class="text-xs font-bold text-gray-400 uppercase">Dirección / Referencias</p>
                                        <p class="text-gray-800 font-semibold mt-0.5"><?php echo htmlspecialchars($datos['direccion'] ?: 'No especificada'); ?></p>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <p class="text-xs font-bold text-gray-400 uppercase">Descripción Corta</p>
                                        <p class="text-gray-600 mt-0.5 italic">"<?php echo htmlspecialchars($datos['descripcion_corta']); ?>"</p>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <p class="text-xs font-bold text-gray-400 uppercase">Descripción Completa</p>
                                        <p class="text-gray-700 mt-0.5 leading-relaxed"><?php echo nl2br(htmlspecialchars($datos['descripcion_completa'])); ?></p>
                                    </div>
                                    <?php if(!empty($datos['facebook']) || !empty($datos['instagram'])): ?>
                                        <div class="sm:col-span-2 pt-2 border-t border-gray-200/60 flex gap-4 text-xs">
                                            <?php if(!empty($datos['facebook'])): ?>
                                                <span class="text-gray-500"><strong>Facebook:</strong> <?php echo htmlspecialchars($datos['facebook']); ?></span>
                                            <?php endif; ?>
                                            <?php if(!empty($datos['instagram'])): ?>
                                                <span class="text-gray-500"><strong>Instagram:</strong> <?php echo htmlspecialchars($datos['instagram']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                            </div>
                        <?php endif; ?>

                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>