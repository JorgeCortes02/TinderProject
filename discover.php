<!DOCTYPE html>
<html lang="en">

<?php
//Llegan los datos del usuario desde el LOGIN
session_start();

?>

<!-- MUESTRA DE CSS MESSAGE sólo al iniciar sesión -->
<script>
    document.addEventListener("DOMContentLoaded", (event) => {
        const loginNotification = <?php echo json_encode($_SESSION['showLoginNotification']); ?>;//metemos en una variable de js
        if (loginNotification == true){
            showNotification("Inicio de sesión exitoso", "success");//mostramos noti
            <?php
            $_SESSION['showLoginNotification'] = false;//lo ponemos a false en la sesion
            ?>
        }
    })
</script>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Discover</title>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="discover.js"></script>
    <link rel="stylesheet" href="styles.css">

    <!--notificaciones css message-->
    <script src="notifications.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="popups.js"></script>

</head>

<body>
    <div class="container">
        <!-- Logo alineado a la izquierda -->
        <div class="header">
            <h1>IETINDER</h1>

        </div>

        <!-- Caja principal de tarjetas -->
        <div class="card-container">

        </div>

        <!-- Controles de acción -->
        <div class="controls">
            <button class="btn dislike" id="dislike-btn">NOP</button>
            <button class="btn like" id="like-btn">YES</button>
        </div>

        <!-- Menú de navegación -->
        <nav class="bottom-nav">
            <h3><a href="#">Descobrir</a></h3>
            <h3><a href="messages.php#">Missatges</a></h3>
            <h3><a href="profile.php">Perfil</a></h3>
        </nav>
    </div>
</body>

</html>


<?php

//Esta función es un andamio que nos permite obtener los datos para nuestro usuario actual que tiene iniciada la sesión hasta que funcione el login.
function recuperarUserDataDePrueba()
{


    try {
        $hostname = "localhost";
        $dbname = "DatingApp";
        $username = "admin";
        $pw = "admin123";
        $pdo = new PDO("mysql:host=$hostname;dbname=$dbname", "$username", "$pw");
    } catch (PDOException $e) {
        echo "Failed to get DB handle: " . $e->getMessage() . "\n";
        exit;
    }

    //preparem i executem la consulta
    $query = $pdo->prepare("SELECT 
                                    IdUser, 
                                    Username, 
                                    Orientation, 
                                    Gender, 
                                    Longitude, 
                                    Latitude, 
                                    Points, 
                                    UserAge
                                FROM User 
                                WHERE IdUser = 1;");
    $query->execute();

    // Obtener el resultado como un arreglo asociativo
    $result = $query->fetch(PDO::FETCH_ASSOC);

    // Almacenar el resultado en la sesión
    $_SESSION['user_data'] = $result;

    //eliminem els objectes per alliberar memòria 
    unset($pdo);
    unset($query);

}



?>