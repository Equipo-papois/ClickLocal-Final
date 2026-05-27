<?php 
require_once 'conexionConfig.php';
require_once 'configuracion_estilos.php';

// Obtener el rol actual del usuario en sesión
$rol_actual = isset($_SESSION['usuario_rol']) ? $_SESSION['usuario_rol'] : 'Visitante';
$id_usuario = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : 0;

// Consultar los negocios activos calculando su promedio de estrellas en tiempo real
$query = $pdo->query("
    SELECT n.*, IFNULL(AVG(c.estrellas), 0) as promedio_estrellas, COUNT(c.id) as total_votos
    FROM negocios n
    LEFT JOIN calificaciones c ON n.id = c.negocio_id
    GROUP BY n.id
    ORDER BY n.id DESC
");
$todos_los_negocios = $query->fetchAll(PDO::FETCH_ASSOC);

// Filtrar colecciones por categoría
$tendencias   = array_slice($todos_los_negocios, 0, 3);
$artesanias   = array_filter($todos_los_negocios, function($b) { return $b['categoria'] === 'Artesanías'; });
$gastronomia  = array_filter($todos_los_negocios, function($b) { return $b['categoria'] === 'Gastronomía'; });
$servicios    = array_filter($todos_los_negocios, function($b) { return $b['categoria'] === 'Servicios'; });

$mis_favoritos = [];
?>

<?php include 'cabecera.php'; ?>

<style>
    .btn-detalles {
        border-color: <?php echo COLOR_PRINCIPAL; ?>;
        color: <?php echo COLOR_PRINCIPAL; ?>;
    }
    .btn-detalles:hover {
        background-color: <?php echo COLOR_PRINCIPAL; ?>;
        color: #ffffff;
    }
    .estrella-btn { 
        transition: transform 0.1s ease, color 0.1s ease; 
    }
    .estrella-btn:hover { 
        transform: scale(1.3); 
        color: #f59e0b; 
    }
</style>

<div class="min-h-screen bg-white">
    <?php include 'menu_lateral.php'; ?>
    
    <div class="lg:ml-64">
        
        <?php if (isset($_GET['status'])): ?>
            <div class="bg-blue-50 border-b border-blue-200 p-4 text-center text-sm font-semibold text-blue-700">
                 Sistema: Movimiento o actualización procesada correctamente en el catálogo.
            </div>
        <?php endif; ?>

        <section class="p-6">
            <div class="p-8 lg:p-12 rounded-[2rem] text-white shadow-xl relative overflow-hidden" 
                 style="background: linear-gradient(to right, <?php echo COLOR_PRINCIPAL; ?>, <?php echo COLOR_PRINCIPAL_OSCURO; ?>);">
                <div class="relative z-10 max-w-xl">
                    <span class="text-xs font-bold uppercase bg-white/20 px-3 py-1 rounded-full tracking-wider mb-3 inline-block">
                        Modo: <?php echo $rol_actual; ?>
                    </span>
                    <h1 class="text-3xl lg:text-4xl font-extrabold tracking-tight mb-3" style="font-family: 'Montserrat', sans-serif;">
                        ¡Hola, <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>!
                    </h1>
                    <p class="text-white/90 text-sm lg:text-base font-normal leading-relaxed" style="font-family: 'Open Sans', sans-serif;">
                        <?php 
                        if ($rol_actual === 'Administrador') {
                            echo "Tienes el control de Click Local. Gestiona, autoriza solicitudes de alta, cambios o bajas de los locatarios.";
                        } elseif ($rol_actual === 'Dueño') {
                            echo "Administra tu negocio publicado. Desde aquí puedes enviar solicitudes de actualización o baja.";
                        } else {
                            echo "Descubre el talento increíble que vive cerca de ti en nuestro catálogo local. Explora y guarda tus favoritos.";
                        }
                        ?>
                    </p>
                </div>
                <div class="absolute right-8 bottom-0 text-7xl opacity-15 pointer-events-none select-none hidden sm:block transform translate-y-2">🛍️</div>
            </div>
        </section>

        <?php if ($rol_actual === 'Administrador'): ?>
            <section class="p-6 mx-6 mb-6 bg-red-50 border border-red-200 rounded-3xl">
                <h2 class="text-lg font-bold text-red-900 mb-2 font-['Montserrat']">Panel de Control de Solicitudes</h2>
                <p class="text-xs text-red-700 mb-4">Aquí se concentran los movimientos de barrio para que los revises, modifiques o des de baja.</p>
                <a href="/clicklocal/vistas/admin_solicitudes.php" class="inline-block bg-red-800 text-white px-5 py-2.5 rounded-xl font-bold text-xs hover:bg-red-900 transition shadow">
                    Ver Solicitudes Pendientes
                </a>
            </section>
        <?php endif; ?>

        <?php
        function renderizarSeccionNegocios($titulo, $id_seccion, $lista_negocios, $rol_actual, $mis_favoritos) {
            echo '<section id="' . $id_seccion . '" class="p-6 scroll-mt-6">';
            echo '  <h2 class="text-2xl font-bold mb-6 text-gray-800 flex items-center gap-2" style="font-family: \'Montserrat\', sans-serif;">' . $titulo . '</h2>';
            
            if (count($lista_negocios) > 0) {
                echo '  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">';
                foreach ($lista_negocios as $negocio) {
                    
                    $promedio = round($negocio['promedio_estrellas'], 1);
                    $categoria_actual = $negocio['categoria'];
                    if ($categoria_actual === 'Gastronomía') {
                        $clases_etiqueta = COLOR_GASTRONOMIA;
                    } elseif ($categoria_actual === 'Artesanías') {
                        $clases_etiqueta = COLOR_ARTESANIAS;
                    } else {
                        $clases_etiqueta = COLOR_SERVICIOS;
                    }
                    ?>
                    <div class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden hover:shadow-lg transition flex flex-col justify-between relative">

                        <div>
                            <img src="<?php echo htmlspecialchars($negocio['imagen_url']); ?>" class="w-full h-48 object-cover">
                            <div class="p-5">
                                <div class="flex justify-between items-center">
                                    <span class="text-[10px] px-2 py-1 rounded-full font-bold uppercase tracking-wider <?php echo $clases_etiqueta; ?>">
                                        <?php echo htmlspecialchars($categoria_actual); ?>
                                    </span>
                                    
                                    <div class="flex items-center gap-1 bg-yellow-50 px-2 py-0.5 rounded-lg border border-yellow-100">
                                        <span class="text-yellow-500 font-bold text-xs">★</span>
                                        <span class="text-xs font-bold text-yellow-700"><?php echo $promedio > 0 ? $promedio : '0.0'; ?></span>
                                        <span class="text-[10px] text-gray-400 font-normal">(<?php echo $negocio['total_votos']; ?>)</span>
                                    </div>
                                </div>

                                <h3 class="font-bold text-xl mt-3 text-gray-800"><?php echo htmlspecialchars($negocio['nombre']); ?></h3>
                                <p class="text-gray-500 text-sm mt-2 line-clamp-2"><?php echo htmlspecialchars($negocio['descripcion_corta']); ?></p>
                                <p class="text-xs text-gray-400 mt-3 flex items-center gap-1"> <?php echo htmlspecialchars($negocio['horario']); ?></p>
                                
                                <?php if ($rol_actual === 'Visitante'): ?>
                                    <div class="mt-4 pt-2 border-t border-dashed border-gray-100 flex items-center justify-between">
                                        <span class="text-xs font-semibold text-gray-400">Califica este negocio:</span>
                                        <div class="flex gap-0.5 group">
                                            <?php for($i=1; $i<=5; $i++): ?>
                                                <a href="/clicklocal/vistas/procesar_calificacion.php?negocio_id=<?php echo $negocio['id']; ?>&voto=<?php echo $i; ?>" 
                                                   title="Calificar con <?php echo $i; ?> estrellas" 
                                                   class="estrella-btn text-lg text-gray-300 hover:text-yellow-500 font-bold transition">★</a>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="p-5 pt-0 space-y-2">
                            <a href="detalle_negocio.php?id=<?php echo $negocio['id']; ?>" class="btn-detalles block text-center border-2 py-2 rounded-xl text-sm font-bold transition duration-300">
                                Ver detalles
                            </a>

                            <?php 
                            $id_sesion_segura = isset($_SESSION['usuario_id']) ? (int)$_SESSION['usuario_id'] : 0;
                            $id_dueno_negocio = isset($negocio['usuario_id']) ? (int)$negocio['usuario_id'] : -1;

                            if ($rol_actual === 'Administrador' || ($rol_actual === 'Dueño' && $id_dueno_negocio === $id_sesion_segura)): 
                            ?>
                                <div class="grid grid-cols-2 gap-2 pt-1 border-t border-gray-100">
                                    <a href="/clicklocal/vistas/solicitar_cambios.php?accion=Modificacion&id=<?php echo (int)$negocio['id']; ?>" class="text-center py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-semibold transition">
                                        Modificar
                                    </a>
                                    <a href="/clicklocal/vistas/solicitar_cambios.php?accion=Baja&id=<?php echo (int)$negocio['id']; ?>" onclick="return confirm('¿Seguro que deseas procesar este movimiento sobre el catálogo?');" class="text-center py-1.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg text-xs font-semibold transition">
                                        Dar Baja
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>

                    </div>
                    <?php
                }
                echo '  </div>';
            } else {
                echo '  <div class="text-center py-8 bg-gray-50 rounded-2xl border border-dashed border-gray-200 text-gray-400 text-sm">No hay negocios registrados en esta categoría aún.</div>';
            }
            echo '</section>';
        }
        ?>

        <?php renderizarSeccionNegocios('Tendencias', 'tendencias', $tendencias, $rol_actual, $mis_favoritos); ?>

        <section id="objetivo" class="p-6 bg-gray-50 border-y border-gray-100 scroll-mt-6">
            <div class="max-w-4xl mx-auto flex flex-col sm:flex-row gap-4 items-start p-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-800 mb-2" style="font-family: 'Montserrat', sans-serif;">Objective</h2>
                    <p class="text-gray-600 text-sm leading-relaxed">Crear una plataforma que facilite la conexión entre micro-emprendedores locales y su comunidad, promoviendo el comercio de proximidad y fortaleciendo la economía local.</p>
                </div>
            </div>
        </section>

        <section id="justificacion" class="p-6 bg-white scroll-mt-6">
            <div class="max-w-4xl mx-auto flex flex-col sm:flex-row gap-4 items-start p-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-800 mb-2" style="font-family: 'Montserrat', sans-serif;">Justificación</h2>
                    <p class="text-gray-600 text-sm leading-relaxed">Muchos talentos locales no cuentan con visibilidad digital ni recursos para competir con grandes plataformas. Click Local permite que pequeños negocios y servicios caseros lleguen directamente a sus vecinos sin intermediarios.</p>
                </div>
            </div>
        </section>

        <section id="alcance" class="p-6 bg-gray-50 border-t border-gray-100 scroll-mt-6">
            <div class="max-w-4xl mx-auto flex flex-col sm:flex-row gap-4 items-start p-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-800 mb-2" style="font-family: 'Montserrat', sans-serif;">Alcance</h2>
                    <p class="text-gray-600 text-sm leading-relaxed">Desde artesanos hasta gastronomía casera, conectamos a todo tipo de emprendedores con su comunidad local, facilitando el acceso a productos y servicios de calidad cercanos.</p>
                </div>
            </div>
        </section>

        <?php renderizarSeccionNegocios('Artesanías', 'artesanias', $artesanias, $rol_actual, $mis_favoritos); ?>
        <?php renderizarSeccionNegocios('Gastronomía', 'gastronomia', $gastronomia, $rol_actual, $mis_favoritos); ?>
        <?php renderizarSeccionNegocios('Servicios', 'servicios', $servicios, $rol_actual, $mis_favoritos); ?>

        <?php if ($rol_actual === 'Visitante'): ?>
            <section class="py-16 px-6 text-center border-t border-red-50" style="background-color: <?php echo COLOR_PRINCIPAL_PASTEL; ?>;">
                <div class="max-w-3xl mx-auto">
                    <h2 class="text-3xl font-bold text-gray-800 mb-4" style="font-family: 'Montserrat', sans-serif;">¿Tienes un emprendimiento o servicio?</h2>
                    <p class="text-gray-600 mb-8 text-base">Únete a Click Local y permite que tus vecinos te encuentren fácilmente. Empieza a digitalizar tu negocio hoy mismo.</p>
                    <a href="/clicklocal/vistas/registro.php" 
                       class="inline-block text-white px-8 py-4 rounded-xl font-bold text-lg transition shadow-lg"
                       style="background-color: <?php echo COLOR_PRINCIPAL; ?>;"
                       onmouseover="this.style.backgroundColor='<?php echo COLOR_PRINCIPAL_OSCURO; ?>'"
                       onmouseout="this.style.backgroundColor='<?php echo COLOR_PRINCIPAL; ?>'">
                        Registra tu negocio aquí
                    </a>
                </div>
            </section>
        <?php endif; ?>

    </div>
</div>

<?php include 'pie_pagina.php'; ?>