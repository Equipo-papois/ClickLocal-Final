<?php require_once '../configuracion_estilos.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Click Local</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://accounts.google.com/gsi/client" async defer></script>
</head>
<body class="bg-gray-50 font-['Open_Sans'] flex items-center justify-center min-h-screen p-4">

    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl border border-gray-100 p-8 text-center">
        
        <div class="mb-6">
            <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-3xl mx-auto mb-3 shadow-sm bg-gray-50">📍</div>
            <h1 class="text-2xl font-bold text-gray-800 font-['Montserrat']">Ingresar a Click Local</h1>
            <p class="text-gray-500 text-sm mt-1">Conéctate al instante con el talento de tu barrio.</p>
        </div>

        <div class="space-y-4 my-8 flex flex-col items-center justify-center">
                    <div id="g_id_onload"
                        data-client_id="922481150595-icobquj33cjfno3hagr2m0p13fndfhso.apps.googleusercontent.com"
                        data-context="signin"
                        data-ux_mode="popup"
                        data-login_uri="http://localhost/clicklocal/vistas/oauth_callback.php"
                        data-auto_prompt="false">
                    </div>

                    <div class="g_id_signin"
                        data-type="standard"
                        data-shape="pill"
                        data-theme="outline"
                        data-text="signin_with"
                        data-size="large"
                        data-logo_alignment="left">
                    </div>
                </div>

        <div class="text-xs text-gray-400 mt-6 border-t border-gray-100 pt-4">
            Al continuar, aceptas la digitalización y apoyo al comercio de proximidad local de Minatitlan y Coatzacoalcos.
        </div>
    </div>

</body>
</html>