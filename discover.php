<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css" type="text/css">
    <title>Discover</title>
    <script src="match.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <link rel="stylesheet" href="styles.css">

</head>



<body>
    <div class="container">
        <!-- Logo alineado a la izquierda -->
        <div id="titleDiv">
            <h1>IETINDER</h1>
        </div>

        <!-- Caja principal de tarjetas -->
        <div class="card-container">
            <div class="card">
                <img src="profile.jpg" alt="Profile" class="card-img">
                <div class="card-info">
                    <h3>Nombre</h3>
                    <p>Descripción breve</p>
                </div>
            </div>
        </div>

        <!-- Controles de acción -->
        <div class="controls">
            <button class="btn dislike" id="dislike-btn">NOP</button>
            <button class="btn like" id="like-btn">YES</button>
        </div>

        <!-- Menú de navegación -->
        <nav class="bottom-nav">
            <h3><a href="#">Descobrir</a></h3>
            <h3><a href="#">Missatges</a></h3>
            <h3><a href="#">Perfil</a></h3>
        </nav>
    </div>
</body>

</html>