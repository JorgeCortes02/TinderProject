<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css" type="text/css">
    <title>Login</title>
</head>

<body id="loginBody">

    <?php    
    // Cuando se ha hecho submit en el form de login
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $email = $_POST['mail'] ?? '';
        $password = $_POST['contrassenya'] ?? '';

        // Limpieza básica de los datos recibidos
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        $password = htmlspecialchars($password);

        // Llama a la función LOGIN con los datos del usuario
        login($email, $password);
    }

    //FUNCION PARA CARGAR DATOS COMPLETOS DEL USUARIO PARA LLEVAR A DISCOVER
    function getUserData($storedUserId){
        try {
            $hostname = "localhost";
            $dbname = "DatingApp";
            $username = "admin";
            $pw = "macarrones con queso";
            $pdo = new PDO("mysql:host=$hostname;dbname=$dbname", "$username", "$pw");
        } catch (PDOException $e) {
            echo "Failed to get DB handle: " . $e->getMessage() . "\n";
            exit;
        }

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
                                WHERE IdUser = :id;");
        $query->bindParam(":id", $storedUserId);
        $query->execute();
        $query->execute();

        // Obtener el resultado como un arreglo asociativo
        $result = $query->fetch(PDO::FETCH_ASSOC);

        // Almacenar el resultado en la sesión
        $_SESSION['user_data'] = $result;

        //eliminem els objectes per alliberar memòria 
        unset($pdo);
        unset($query);

    }


    //FUNCION LOGIN
    function login($email, $password)
    {
        try {
            $hostname = "localhost";
            $dbname = "DatingApp";
            $username = "admin";
            $pw = "macarrones con queso";

            // Conexión a la base de datos
            $pdo = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $pw);
        } catch (PDOException $e) {
            echo "<p>Failed to connect to the database: " . $e->getMessage() . "</p>";
            exit;
        }

        // Paso 1: Verificar si el email existe, si existe nos quedamos con su password y su ID
        $query = $pdo->prepare("SELECT Password, IdUser FROM User WHERE Email = :mail");
        $query->bindParam(":mail", $email);
        $query->execute();
        $row = $query->fetch();

        // si el email NO existe
        if (!$row) {
            ?>
            <script>
                document.addEventListener("DOMContentLoaded", (event) => {
                    Array.from(document.getElementsByTagName("input"))[0].style.borderColor = "red"; //borde rojo en input
                    document.getElementById("errorEmail").style.display = "block"; //mensaje en display
                })
            </script> <?php


        //si el email SÍ existe
        } else {
            // Paso 2: Verificar si la contraseña es correcta
            $storedPassword = $row['Password'];

            //si la contraseña es incorrecta
            if ($storedPassword !== $password) {
                ?>
                <script>
                    document.addEventListener("DOMContentLoaded", (event) => {
                        Array.from(document.getElementsByTagName("input"))[1].style.borderColor = "red"; //borde rojo en input
                        document.getElementById("errorPassword").style.display = "block"; //mensaje en display
                    })
                </script> <?php

            //si todo es correcto
            } else {
                // Seleccionamos el Id que hemos recuperado
                $storedUserId = $row['IdUser'];
                
                //Cargamos los datos del usuario en la sesion
                session_start();
                getUserData($storedUserId);

                //redireccionamos a DISCOVER
                header("Location: discover.php/");

            }
        }
        // Cierra las conexiones
        unset($pdo);
        unset($query);
    }
    ?>

    <h1>IETINDER</h1>
    <h3>App de ligoteo</h3>
    <form method="POST">
        <!-- Campo Email -->
        <label for="mail">Email</label></br>
        <input type="email" name="mail" required>
        <p id="errorEmail">El correu no està registrat</p>
        </p>
        </br>

        <!-- Campo Contraseña -->
        <label for="contrassenya">Contrassenya:</label></br>
        <input type="password" name="contrassenya" required>
        <p id="errorPassword">Contrassenya incorrecta</p>
        </p>
        </br>

        <button type="submit">Iniciar sessió</button>
    </form>

    <a href="">¿Has oblidat la contrassenya?</a>
    </br>
    <a href="">Crear una compte nova</a>
</body>

</html>