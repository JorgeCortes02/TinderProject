<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
</head>
<body>
<?php
    include_once '../apis.php'; 

    // Paso 1: recoge la URL desde donde viene, si no hay te dirige al error
    if (!isset($_SERVER['HTTP_REFERER'])) {
        logServer("Usuario no identificado ha intentado entrar en el panel de administración");
        header("Location: /errors/error403.php");
        exit();
    }
?>
    
</body>
</html>