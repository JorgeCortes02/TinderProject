<?php 
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    } 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="module" src="photos.js"></script>
    <script src="notifications.js"></script>

    <link rel="stylesheet" href="styles.css">

    <title>Editar mis fotos</title>
</head>

<body id="bodyPhotos">

    <div class="container containerFromViewPhoto">

        <?php include('./header.php') ?>

            <main>

                <h2>Mis fotografías</h2>

                <div id="containerPhotos"></div>

            </main>

        <?php include('./footer.php') ?>

    </div>

</body>

</html>