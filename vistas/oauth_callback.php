<?php
require_once '../conexionConfig.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['credential'])) {
    $jwt = $_POST['credential'];
    

    $partes = explode('.', $jwt);
    if (count($partes) === 3) {
        $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $partes[1])), true);
        
        if ($payload && isset($payload['sub'])) {
            $oauth_id = $payload['sub'];
            $nombre   = $payload['name'];
            $email    = strtolower(trim($payload['email'])); // Convertimos a minúsculas para comparar seguro
            $foto     = $payload['picture'];


            $rol_asignado = ($email === 'c.joelperez@gmail.com') ? 'Administrador' : 'Visitante';

            // 1. Verificar si el usuario ya existe en nuestra base de datos
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE oauth_id = ? AND oauth_proveedor = 'Google'");
            $stmt->execute([$oauth_id]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$usuario) {

                $insert = $pdo->prepare("INSERT INTO usuarios (oauth_id, oauth_proveedor, nombre, email, foto_perfil, rol) VALUES (?, 'Google', ?, ?, ?, ?)");
                $insert->execute([$oauth_id, $nombre, $email, $foto, $rol_asignado]);
                
                $id_tabla = $pdo->lastInsertId();
                $rol_usuario = $rol_asignado;
            } else {

                if ($email === 'c.joelperez@gmail.com' && $usuario['rol'] !== 'Administrador') {
                    $update_admin = $pdo->prepare("UPDATE usuarios SET rol = 'Administrador' WHERE id = ?");
                    $update_admin->execute([$usuario['id']]);
                    $rol_usuario = 'Administrador';
                } else {
                    $id_tabla = $usuario['id'];
                    $rol_usuario = $usuario['rol'];
                }
                $nombre = $usuario['nombre'];
            }


            $_SESSION['usuario_id'] = $id_tabla;
            $_SESSION['usuario_nombre'] = $nombre;
            $_SESSION['usuario_rol'] = $rol_usuario;
            $_SESSION['usuario_foto'] = $foto;


            header("Location: ../index.php");
            exit;
        }
    }
}
header("Location: login.php");
exit;