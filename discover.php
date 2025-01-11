<!DOCTYPE html>
<html lang="en">
<?php

session_start();

//Ejecutamos el andamio para poder tener datos del usuario que tiene iniciada la sesion. Más información en el bloque php donde se crea la función.
recuperarUserDataDePrueba();
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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


<?php

//Esta función es un andamio que nos permite obtener los datos para nuestro usuario actual que tiene iniciada la sesión hasta que funcione el login.
function recuperarUserDataDePrueba()
{


    try {
        $hostname = "localhost";
        $dbname = "DatingApp";
        $username = "root";
        $pw = "1234";
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
                                    MaxAge, 
                                    MinAge, 
                                    UserAge
                                FROM User 
                                WHERE IdUser = 1;");
    $query->execute();

    // Obtener el resultado como un arreglo asociativo
    $result = $query->fetch(PDO::FETCH_ASSOC);

    // Almacenar el resultado en la sesión
    $_SESSION['user_data'] = $result;
    print_r($_SESSION['user_data']);
    //eliminem els objectes per alliberar memòria 
    unset($pdo);
    unset($query);

}


?>
