<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/styles.css">

    <title>No autorizado</title>
</head>

<body id="bodyErrors">
    <div>

        <?php include($_SERVER['DOCUMENT_ROOT'] . '/header.php'); ?>

        <main>
            <h1>Error 401 - No Autorizado</h1>
            <h2>Lo sentimos, no tienes autorización para acceder a esta página.</h2>
            <p>Esto puede deberse a varias razones:</p>
            <ul>
                <li>No has iniciado sesión o tu sesión ha expirado.</li>
                <li>Estás intentando acceder a un contenido restringido sin los permisos adecuados.</li>
                <li>Tu cuenta no tiene autorización para ver esta página.</li>
            </ul>
            <p>Por favor, inicia sesión con una cuenta válida o contacta con el administrador si crees que esto es un error.</p>
            <a href="/login.php">Volver a la página principal</a>
        </main>
        
    </div>
    
</body>

</html>