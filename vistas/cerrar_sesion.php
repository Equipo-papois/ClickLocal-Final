<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Vaciamos y destruimos la sesión de PHP
$_SESSION = array();
session_destroy();

// Redireccionamos al login
header("Location: login.php");
exit;