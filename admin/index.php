<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
</head>
<body>
<?php
    // Paso 1: recoge la URL desde donde viene
    if (isset($_SERVER['HTTP_REFERER'])) {

        // Paso 2: Verifica si la URL contiene "/login.php"
        if (strpos($referer, '/login.php') === false) {
            logServer("Usuario no identificado ha intentado entrar en el panel de administración");
            header("Location: /login.php");
            exit;
        }
    }
    else {
        echo "No se pudo determinar el sitio de origen.";
        // Redirige al la página de error403
        header("Location: /errors/error403.php");
        exit();
    }
?>
    
</body>
</html>