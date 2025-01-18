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
            logServer('Sesión activa, redirigiendo a discover.php');
            header("Location: discover.php");

            exit();
        } else {
            // Si no hay sesión activa, redirigir a otra página
            logServer('No se ha encontrado una sesón, redirigiendo a login.php');
            header("Location: login.php");
            exit();
        }
    
    ?>
</body>

</html>