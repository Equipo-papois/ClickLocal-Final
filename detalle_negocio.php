<?php 
require_once 'conexionConfig.php';

// Validar que venga un ID por la URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = intval($_GET['id']);

// Buscar solo ese negocio
$stmt = $pdo->prepare("SELECT * FROM negocios WHERE id = ?");
$stmt->execute([$id]);
$negocio = $stmt->fetch(PDO::FETCH_ASSOC);

// Si el negocio no existe, regresar al inicio
if (!$negocio) {
    header("Location: index.php");
    exit;
}
?>

<?php include 'cabecera.php'; ?>

<div class="min-h-screen bg-white">
    <?php include 'menu_lateral.php'; ?>
    
    <div class="lg:ml-64 p-6">
        <a href="index.php" class="inline-flex items-center gap-2 text-pink-500 font-semibold mb-6 hover:text-pink-600 transition">
            ⬅ Volver al inicio
        </a>

        <div class="max-w-5xl mx-auto bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden grid grid-cols-1 md:grid-cols-2">
            <div class="h-96 md:h-full">
                <img src="<?php echo htmlspecialchars($negocio['imagen_url']); ?>" class="w-full h-full object-cover">
            </div>

            <div class="p-8 flex flex-col justify-between">
                <div>
                    <span class="text-xs px-3 py-1 bg-pink-100 text-pink-600 rounded-full font-bold uppercase">
                        <?php echo htmlspecialchars($negocio['categoria']); ?>
                    </span>
                    <h1 class="text-3xl font-bold text-gray-800 mt-4 mb-4"><?php echo htmlspecialchars($negocio['nombre']); ?></h1>
                    <p class="text-gray-600 leading-relaxed mb-6"><?php echo nl2br(htmlspecialchars($negocio['descripcion_completa'])); ?></p>
                    
                    <div class="space-y-3 border-t border-gray-100 pt-4">
                        <p class="text-gray-700 flex items-center gap-2">
                             <strong class="text-gray-900">Horario:</strong> <?php echo htmlspecialchars($negocio['horario']); ?>
                        </p>
                        <p class="text-gray-700 flex items-center gap-2">
                             <strong class="text-gray-900">Ubicación:</strong> <?php echo htmlspecialchars($negocio['tipo_ubicacion'] ?? 'Punto de entrega público'); ?>
                        </p>
                        <?php if(!empty($negocio['direccion'])): ?>
                            <p class="text-gray-500 text-sm pl-6 bg-gray-50 p-2 rounded-lg">
                                 <?php echo htmlspecialchars($negocio['direccion']); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-8">
                    <a href="https://wa.me/52<?php echo $negocio['whatsapp']; ?>" target="_blank" class="flex items-center justify-center gap-2 bg-[#25D366] text-white py-3 px-4 rounded-xl font-bold shadow-md hover:bg-[#20ba5a] transition">
                        💬 WhatsApp
                    </a>

                    <?php if (!empty($negocio['facebook'])): ?>
                        <a href="<?php echo htmlspecialchars($negocio['facebook']); ?>" target="_blank" class="flex items-center justify-center gap-2 bg-[#1877F2] text-white py-3 px-4 rounded-xl font-bold shadow-md hover:bg-[#166fe5] transition">
                            🌐 Facebook
                        </a>
                    <?php elseif (!empty($negocio['instagram'])): ?>
                        <a href="<?php echo htmlspecialchars($negocio['instagram']); ?>" target="_blank" class="flex items-center justify-center gap-2 bg-gradient-to-r from-[#833AB4] via-[#FD1D1D] to-[#FFC0CB] text-white py-3 px-4 rounded-xl font-bold shadow-md transition">
                            📸 Instagram
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'pie_pagina.php'; ?>