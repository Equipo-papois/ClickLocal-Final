<?php 
require_once 'configuracion_estilos.php'; 

// Extraemos los datos reales de la sesión activa de Google
$nombre_usuario = isset($_SESSION['usuario_nombre']) ? $_SESSION['usuario_nombre'] : 'Invitado';
$foto_usuario   = isset($_SESSION['usuario_foto']) ? $_SESSION['usuario_foto'] : null;
$rol_usuario    = isset($_SESSION['usuario_rol']) ? $_SESSION['usuario_rol'] : 'Visitante';
?>

<button onclick="toggleMenuMovil()" class="lg:hidden fixed top-4 left-4 z-[60] bg-white rounded-xl p-3 shadow-md border border-gray-100 hover:scale-105 transition">
    <span id="icono-menu" class="text-xl block">☰</span>
</button>

<div id="capa-oscura" onclick="toggleMenuMovil()" class="lg:hidden fixed inset-0 bg-black/40 z-40 hidden opacity-0 transition-opacity duration-300"></div>

<nav id="menu-lateral" class="fixed left-0 top-0 h-full w-64 bg-white shadow-lg lg:shadow-sm lg:border-r lg:border-gray-100 z-50 flex flex-col -translate-x-full lg:translate-x-0 transition-transform duration-300">
    
    <div class="p-6 border-b border-gray-100">
        <a href="/clicklocal/index.php" class="block">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gray-50 rounded-2xl flex items-center justify-center text-2xl shadow-sm">📍</div>
                <span class="text-2xl font-bold text-gray-800 tracking-tight" style="font-family: 'Montserrat', sans-serif;">
                    Click<span style="color: <?php echo COLOR_PRINCIPAL; ?>;">Local</span>
                </span>
            </div>
        </a>
    </div>

    <div class="flex-1 overflow-y-auto p-4 flex flex-col justify-between">
        <div class="space-y-6">
            <div>
                <p class="px-3 mb-3 text-xs font-bold text-gray-400 uppercase tracking-wider" style="font-family: 'Montserrat', sans-serif;">Navegación</p>
                <nav class="space-y-1">
                    <a href="/clicklocal/index.php" class="flex items-center gap-3 text-white px-4 py-3 rounded-xl font-semibold shadow-md transition duration-200" style="background-color: <?php echo COLOR_PRINCIPAL; ?>;">
                        <span></span> Inicio
                    </a>
                    <a href="/clicklocal/index.php#tendencias" onclick="cerrarMenuAlDarClic()" 
                       class="flex items-center gap-3 text-gray-600 px-4 py-3 rounded-xl font-medium transition duration-200"
                       onmouseover="this.style.color='<?php echo COLOR_PRINCIPAL; ?>'; this.style.backgroundColor='<?php echo COLOR_PRINCIPAL_PASTEL; ?>'"
                       onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'">
                        <span></span> Tendencias
                    </a>
                </nav>
            </div>

            <div>
                <p class="px-3 mb-3 text-xs font-bold text-gray-400 uppercase tracking-wider" style="font-family: 'Montserrat', sans-serif;">Categorías</p>
                <nav class="space-y-1">
                    <?php
                    $cats = [['artesanias', '', 'Artesanías'], ['gastronomia', '', 'Gastronomía'], ['servicios', '', 'Servicios']];
                    foreach ($cats as $c) {
                        ?>
                        <a href="/clicklocal/index.php#<?php echo $c[0]; ?>" onclick="cerrarMenuAlDarClic()" 
                           class="flex items-center gap-3 text-gray-600 px-4 py-3 rounded-xl font-medium transition duration-200"
                           onmouseover="this.style.color='<?php echo COLOR_PRINCIPAL; ?>'; this.style.backgroundColor='<?php echo COLOR_PRINCIPAL_PASTEL; ?>'"
                           onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'">
                            <span><?php echo $c[1]; ?></span> <?php echo $c[2]; ?>
                        </a>
                    <?php } ?>
                </nav>
            </div>

            <?php if ($rol_usuario === 'Administrador'): ?>
            <div>
                <p class="px-3 mb-3 text-xs font-bold text-red-400 uppercase tracking-wider" style="font-family: 'Montserrat', sans-serif;">Consola</p>
                <nav class="space-y-1">
                    <a href="/clicklocal/vistas/admin_solicitudes.php" 
                       class="flex items-center gap-3 text-red-700 px-4 py-3 rounded-xl font-semibold bg-red-50 hover:bg-red-100 transition duration-200">
                        <span></span> Moderar Solicitudes
                    </a>
                </nav>
            </div>
            <?php endif; ?>
        </div>

        <div class="pt-4 border-t border-gray-100 mt-6">
            <div class="flex items-center gap-3 px-2 mb-4">
                <?php if ($foto_usuario): ?>
                    <img src="<?php echo htmlspecialchars($foto_usuario); ?>" alt="Perfil" class="w-10 h-10 rounded-full border border-gray-200 object-cover">
                <?php else: ?>
                    <div class="w-10 h-10 rounded-full bg-gray-100 border border-gray-200 flex items-center justify-center text-xl">👤</div>
                <?php endif; ?>
                <div class="overflow-hidden">
                    <p class="text-sm font-bold text-gray-800 truncate" style="font-family: 'Montserrat', sans-serif;">
                        <?php echo htmlspecialchars($nombre_usuario); ?>
                    </p>
                    <span class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase tracking-wider inline-block mt-0.5" 
                          style="background-color: <?php echo COLOR_PRINCIPAL_PASTEL; ?>; color: <?php echo COLOR_PRINCIPAL; ?>;">
                        <?php echo $rol_usuario; ?>
                    </span>
                </div>
            </div>
            
            <a href="/clicklocal/vistas/cerrar_sesion.php" class="flex items-center gap-3 text-gray-500 px-4 py-2.5 rounded-xl text-xs font-medium hover:bg-gray-50 hover:text-red-600 transition duration-200">
                <span></span> Cerrar Sesión
            </a>
        </div>
    </div>
</nav>

<script>
    function toggleMenuMovil() {
        const menu = document.getElementById('menu-lateral');
        const capa = document.getElementById('capa-oscura');
        if (menu.classList.contains('-translate-x-full')) {
            menu.classList.remove('-translate-x-full');
            menu.classList.add('translate-x-0');
            capa.classList.remove('hidden');
            setTimeout(() => capa.classList.remove('opacity-0'), 10);
        } else {
            menu.classList.remove('translate-x-0');
            menu.classList.add('-translate-x-full');
            capa.classList.add('opacity-0');
            setTimeout(() => capa.classList.add('hidden'), 300);
        }
    }
    function cerrarMenuAlDarClic() { if (window.innerWidth < 1024) toggleMenuMovil(); }
</script>