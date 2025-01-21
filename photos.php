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

    <link rel="stylesheet" href="styles.css">
    <style>

        .container{
            justify-content: flex-start;
        }

        #containerPhotos {
            display: flex;
            flex-wrap: wrap;
            gap: 10px; /* Espaciado entre los paneles */
        }

        .newPhotoPanel {
            width: 150px; /* Ancho de los paneles */
            height: 150px; /* Alto de los paneles, creando un cuadrado */
            background-color: #d3d3d3; /* Color gris de fondo */
            border: 1px solid #ccc; /* Un borde sutil */
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            position: relative; /* Esto es necesario para que el botón se posicione en relación a este contenedor */
        }

        .newPhotoPanel img {
            max-width: 100%;
            max-height: 100%;
            object-fit: cover; /* Asegura que la imagen se ajuste bien */
        }

        /* Estilo para el botón de cierre */
        .close-btn {
            position: absolute;
            top: 5px;   /* Ajusta la distancia desde la parte superior */
            right: 5px; /* Ajusta la distancia desde la parte derecha */
            width: 20px; /* Tamaño del botón de cierre */
            height: 20px; /* Tamaño del botón de cierre */
            cursor: pointer; /* Cambia el cursor al pasar sobre el botón */
        }


    </style>

    <title>Editar mis fotos</title>
</head>

<body>

    <div class="container">

        <?php include('./header.php') ?>

            <main>

                <h2>Mis fotografías</h2>

                <div id="containerPhotos"></div>

            </main>

        <?php include('./footer.php') ?>

    </div>

</body>

</html>