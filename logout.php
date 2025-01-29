
<?php 
include 'config.php';
include 'apis.php'; 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function logServer($message) {
    error_log($message); // Asegúrate de que `logServer` esté definido o usa `error_log`
}

logServer("Eliminando sesión...");

// Limpiar variables de sesión
$_SESSION = [];

// Destruir la sesión
session_destroy();

// Redirigir al usuario a la página de inicio de sesión
header("Location: login.php");
exit;
?>
<!DOCTYPE html>
<html lang="en">
  
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css" type="text/css">
    <title>Logout</title>
</head>
<body>
    
</body>
</html>