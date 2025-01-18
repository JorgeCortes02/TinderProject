<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css" type="text/css">
    <title>Index</title>
</head>

<body>
    <?php 
        
        // Iniciar la sesión
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        include_once 'apis.php'; 
include 'config.php';
        // Verificar si hay una sesión activa
        if (isset($_SESSION['user_data'])) {
            // Si la sesión está activa, redirigir a la página correspondiente
            header("Location: discover.php");
            registrarLog("Existe sesion, se redirige a dicover");
            exit();
        } else {
            // Si no hay sesión activa, redirigir a otra página
            header("Location: login.php");
            registrarLog("No existe sesion, se redirige a login");
            exit();
        }
    
    ?>
</body>

</html>