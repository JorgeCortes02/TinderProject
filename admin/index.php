<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="styles.css" type="text/css">
    
    <title>Admin Panel</title>
</head>

<?php

    session_start();

    include_once '../apis.php'; 

    // si no estás identificado -> error403
    if (!isset($_SESSION['user_data'])) {
        logServer("Acceso no autorizado: usuario no identificado ha intentado entrar en el panel de administración");
        http_response_code(403);
        exit();
    }

    // si estás identificado pero no tienes los permisos -> error401
    if ($_SESSION['user_data']['Role'] !== 'Admin') {
        logServer("Acceso denegado: usuario con ID {$_SESSION['user_data']['ID']} intentó acceder sin permisos de administrador.");
        http_response_code(401);
        exit();
    }

?>

<body id="adminIndex">

    <?php include('header.php'); ?>
    <h1>Bienvenido al Panel de Administración</h1>

    <main>
    
        <a href="users.php">Listar usuarios</a>
        <a href="logs.php">Listar logs</a>

    </main>

</body>
</html>