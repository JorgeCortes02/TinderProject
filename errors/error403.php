<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/styles.css">

    <title>Página prohibida</title>
</head>

<body id="bodyErrors">
    <div>

        <?php include($_SERVER['DOCUMENT_ROOT'] . '/header.php'); ?>

        <main>
            <h1>Error 403 - Acceso Prohibido</h1>
            <h2>Lo sentimos, no tienes permiso para acceder a esta página.</h2>
            <p>Esto puede deberse a varias razones:</p>
            <ul>
                <li>No tienes los permisos necesarios para visualizar este contenido.</li>
                <li>Estás intentando acceder a un área restringida del sitio.</li>
                <li>El acceso a esta página ha sido bloqueado intencionadamente.</li>
            </ul>
            <p>Si crees que esto es un error, por favor, contacta con el administrador del sitio o verifica que estás utilizando las credenciales correctas.</p>
            <a href="/login.php">Volver a la página principal</a>
        </main>
        
    </div>
    
</body>

</html>