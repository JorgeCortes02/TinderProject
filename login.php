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
    include_once 'apis.php'; 
    include_once 'config.php';

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
    function getUserData($storedUserId)
    {
        try {
            global $username, $pw;
            $hostname = "localhost";
            $dbname = "DatingApp";
            $pdo = new PDO("mysql:host=$hostname;dbname=$dbname", "$username", "$pw");
        } catch (PDOException $e) {
            echo "Failed to get DB handle: " . $e->getMessage() . "\n";
            registrarLog("Login - Failed to get DB handle: " . $e->getMessage(), 'ERROR');
            exit;
        }

        $query = $pdo->prepare("SELECT 
                                    IdUser,
                                    FirstName,
                                    LastName1,
                                    LastName2,
                                    Username,
                                    BirthDate, 
                                    Orientation, 
                                    Gender, 
                                    Longitude, 
                                    Latitude, 
                                    Points,
                                    UserAge,
                                    MaxAge,
                                    MinAge,
                                    MaxDis,
                                    Bio
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
            global $username, $pw;
            $hostname = "localhost";
            $dbname = "DatingApp";
            
            // Conexión a la base de datos
            $pdo = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $pw);
        } catch (PDOException $e) {
            echo "<p>Failed to connect to the database: " . $e->getMessage() . "</p>";
            registrarLog("Login - Failed to connect to the database in login: " . $e->getMessage(),'ERROR');
            exit;
        }

        // Paso 1: Verificar si el email existe, si existe nos quedamos con su password y su ID
        $query = $pdo->prepare("SELECT Password, IdUser FROM User WHERE Email = :mail");
        $query->bindParam(":mail", $email);
        $query->execute();
        $row = $query->fetch();

        // si el email NO existe
        if (!$row) {
            registrarLog("Email no registrado $email");
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
            if ($storedPassword !== hash('sha256', $password)) {
               
                ?>
                <script>
                    document.addEventListener("DOMContentLoaded", (event) => {
                        Array.from(document.getElementsByTagName("input"))[1].style.borderColor = "red"; //borde rojo en input
                        document.getElementById("errorPassword").style.display = "block"; //mensaje en display
                    })
                </script>
                <?php


            //si todo es correcto
            } else {
               
                // Seleccionamos el Id que hemos recuperado
                $storedUserId = $row['IdUser'];

                //Cargamos los datos del usuario en la sesion
                session_start();
                
                getUserData($storedUserId);

                //Preparamos para que salga una notificacion de inicio de sesión
                $_SESSION['showLoginNotification'] = true;
                //redireccionamos a DISCOVER
              
                header("Location: discover.php");

            }
        }
        // Cierra las conexiones
        unset($pdo);
        unset($query);
    }
    ?>


    <div id="loginContainer">
        <h1>IETINDER</h1>
        <h3>App de ligoteo</h3>
        <h4 id="errorEmail">Error: El correo no está registrado</h4>
        <h4 id="errorPassword">Error: Contraseña incorrecta</h4>


        <form method="POST">
            <!-- Campo Email -->
            <label for="mail">Email:</label></br>
            <input type="email" name="mail" value="<?php if (isset($email)) htmlspecialchars($email); ?>" required>
            </br>


            <!-- Campo Contraseña -->
            <label for="contrassenya">Contraseña:</label></br>
            <input type="password" name="contrassenya" required>
            </br>


            <button type="submit">Iniciar sesión</button>
        </form>


        <a href="">¿Has olvidado la contraseña?</a>
        </br>
        <a href="">Crear una cuenta nueva</a>
    </div>


</body>

</html>
