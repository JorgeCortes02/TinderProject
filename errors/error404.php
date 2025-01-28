<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/styles.css">

    <title>Página no encontrada</title>
</head>

<body id="bodyErrors">
    <div>

        <?php include($_SERVER['DOCUMENT_ROOT'] . '/header.php'); ?>

        <main>
            <h1>Error 404 - Página no encontrada</h1>
            <h2>Lo sentimos, la página que estás buscando no existe.</h2>
            <p>Esto puede deberse a varias razones:</p>
            <ul>
                <li>La página ha sido eliminada o movida.</li>
                <li>Has escrito mal la dirección en el navegador.</li>
                <li>El enlace que seguiste está desactualizado.</li>
            </ul>
            <p>Por favor, verifica la URL </p>
            <p>Gracias por tu comprensión.</p>
            <a href="/login.php">Volver a la página principal</a>
            
        </main>
        
    </div>
    
</body>

</html>