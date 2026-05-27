<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 🎨 PANEL DE CONTROL DE COLORES DE CLICK LOCAL
define('COLOR_PRINCIPAL', '#800020');        
define('COLOR_PRINCIPAL_OSCURO', '#4a0012'); 
define('COLOR_PRINCIPAL_PASTEL', '#fdf2f4'); 

define('COLOR_GASTRONOMIA', 'bg-orange-100 text-orange-600');
define('COLOR_ARTESANIAS', 'bg-purple-100 text-purple-600');
define('COLOR_SERVICIOS', 'bg-blue-100 text-blue-600');


$pagina_actual = basename($_SERVER['PHP_SELF']);
if (!isset($_SESSION['usuario_rol']) && $pagina_actual !== 'login.php' && $pagina_actual !== 'oauth_callback.php') {
    header("Location: /clicklocal/vistas/login.php");
    exit;
}
?>