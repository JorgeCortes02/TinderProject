<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'apis.php'; 
include 'config.php';

// Función para cargar el archivo .env
function loadEnv($path) {
    if (!file_exists($path)) {
        logServer('El archivo .env no se encuentra en la ruta especificada.','ERROR');
        throw new Exception("El archivo .env no se encuentra en la ruta especificada.");
        
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);

        if (!array_key_exists($name, $_ENV)) {
            $_ENV[$name] = $value;
        }
        if (!array_key_exists($name, $_SERVER)) {
            $_SERVER[$name] = $value;
        }
    }
}
// Llama a la función para cargar las variables de entorno
loadEnv(__DIR__ . '/.env');

if (isset($_POST['action']) && $_POST['action'] == 'create_user') {
    $newUserData = [
        'email' => $_POST['email'],
        'passw' => $_POST['passw'],
        'firstName' => $_POST['firstName'],
        'lastName1' => $_POST['lastName1'],
        'lastName2' => $_POST['lastName2'],
        'userName' => $_POST['userName'],
        'birthDate' => $_POST['birthDate'],
        'bio' => $_POST['bio'],
        'gender' => $_POST['gender'],
        'orientation' => $_POST['orientation'],
        'latitude' => $_POST['latitude'],
        'longitude' => $_POST['longitude'],
        'minAge' => $_POST['minAge'],
        'maxAge' => $_POST['maxAge']
    ];

    createUser($newUserData);
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script type="module" src="register.js"></script>
    <title>Register</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="styles.css">

</head>
<body id ="register">
    <div class="containerMessage">
        <!-- Encabezado -->
        <div class="header">
            <h1>IETINDER - Registrate!</h1>
        </div>

        <div class="fieldsContainer">
        <div id = "scroll"></div>
        <div class="error-message" id="errorMessage">
            <h3 class="error">Error: No se puede dejar un campo vacío</h3>
        </div>
        <div class="error-password" id="errorPassword">
            <h3 class="error">Error: Las contraseñas no coinciden</h3>
        </div>
        <div class="error-mail" id="errorMail">
            <h3 class="error">Error: Formato de mail no valido</h3>
        </div>
        <form id="registerForm">

            <h2>Crea tu perfil</h2>
<!-- User Info-->
            <div class ="field">
                <h3>Email: </h3>
                <input type="email" id="email" placeholder="xxxx@ieti.site" required>
                </div>
            <div class ="field">
                <h3>Contraseña: </h3>
                <input type="password" id="password" placeholder="******" required>
                </div>
            <div class ="field">
                <h3>Repetir Contraseña: </h3>
                <input type="password" id="password2" placeholder="******" required>
                </div>
            
            <div class ="field">
                <h3>Nombre: </h3>
                <input type="text" id="firstName" placeholder="Ernesto" required>
                </div>
                <!-- <small id="nombreError" style="color: red; display: none;">Name is required</small> -->
            <div class ="field">
                <h3>Apellidos: </h3>
                <input type="text" id="lastName1" placeholder="Gomez" required>
                <input type="text" id="lastName2" placeholder="Martinez" required>
            </div>
            <div class ="field">
                <h3>Alias: </h3>
                <input type="text" id="userName" placeholder="Terreneitor33" required>
                </div>
            <div class ="field">
                <h3>Fecha de Nacimiento: </h3>
                <input type="date" id="birthdate"required>
                </div>
            <div class ="field radioField">
                <h3>Género: </h3>
                <label>
                    <input type="radio" name="gender" value="Hombre" required>
                    Hombre
                </label>
                </br>
                <label>
                    <input type="radio" name="gender" value="Mujer" required>
                    Mujer
                </label>
                </br>
                <label>
                    <input type="radio" name="gender" value="No Binario" required>
                    No Binario
                </label>
            </div>
            <div class ="field radioField">
                <h3>Orientación: </h3>
                <label>
                    <input type="radio" name="orientacion" value="Heterosexual"required>
                    Heterosexual
                </label>
                </br>
                <label>
                    <input type="radio" name="orientacion" value="Homosexual"required>
                    Homosexual
                </label>
                </br>
                <label>
                    <input type="radio" name="orientacion" value="Bisexual" required>
                    Bisexual
                </label>
            </div>
            <div class ="field">
                <h3>Biografia: </h3>
                <textarea id="bio" rows="10" cols ="50" placeholder="Me gusta el buen vino y leer bajo un arbol al atardecer..." required></textarea>
                </div>
            <div class ="field">
                <h3>Tu localización: </h3>
                <!-- API DE GOOGLE MAPS PARA ELEGIR COORDS (PRIMERO LATITUD Y DESPUES LONGITUD)-->
                <div id="map" style="height: 400px; width: 100%;"></div>
                <input type="text" id="latitude" value="41.355866" required>
                <input type="text" id="longitude" value="2.077589" required>
            </div>

            <div class ="field">
                <h3>Fotografias: </h3>
                <!-- Aqui van un insert de las dos primeras fotos-->

            </div>
            <div class="bottom">
            <button class="createProfileButton" id="createProfileButton">Crear Perfil</button>
            </div>
            </form>
        </div>

        <!-- Menú de navegación inferior -->
        <nav class="bottom-nav">
            <a href="discover.php">Descubrir</a>
            <a href="messages.php" class="active">Mensajes</a>
            <a href="profile.php">Perfil</a>
        </nav>
    </div>
    <script>
        let map;
        let marker;
        /* Valores por defecto */
        function initMap() { 
            const initialLocation = { 
                lat: parseFloat( '41.355866'), 
                lng: parseFloat( '2.077589') 
            };

            // Crear el mapa
            map = new google.maps.Map(document.getElementById("map"), {
                center: initialLocation,
                zoom: 12,
            });

            // Crear el marcador
            marker = new google.maps.Marker({
                position: initialLocation,
                map: map,
                draggable: true, // Hacer el marcador arrastrable
            });

            // Actualizar lat y lon cuando el marcador es movido
            google.maps.event.addListener(marker, 'dragend', function() {
                const position = marker.getPosition();
                document.getElementById('latitude').value = position.lat();
                document.getElementById('longitude').value = position.lng();
            });
        }

    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo $_ENV['GOOGLE_MAPS_API_KEY']; ?>&callback=initMap&libraries=&v=weekly" async defer></script>
</body>
</html>

<?php

function createUser($newUserData){
    try {
        global $username, $pw;
        $hostname = "localhost";
        $dbname = "DatingApp";
        $pdo = new PDO("mysql:host=$hostname;dbname=$dbname", "$username", "$pw");
    } catch (PDOException $e) {
        echo "Failed to get DB handle: " . $e->getMessage() . "\n";
        logServer("Failed to connect to the database" . $e->getMessage(),'ERROR');
        exit;
    }

    // Realiza el hash de la contraseña con SHA-256
    $hashedPassword = hash('sha256', $newUserData['passw']);

    $query = $pdo->prepare("INSERT INTO User SET
                                Email = :email,
                                Password = :passw,
                                FirstName = :firstName,
                                LastName1 = :lastName1,
                                LastName2 = :lastName2,
                                Username = :userName,
                                BirthDate = :birthDate,
                                Orientation = :orientation, 
                                Gender = :gender, 
                                Longitude = :longitude, 
                                Latitude = :latitude, 
                                Bio = :bio,
                                MinAge = :minAge,
                                MaxAge = :maxAge,
                                Points = 0,
                                UserAge = TIMESTAMPDIFF(YEAR, :birthDate, CURDATE())
                            ");
    // bindParam
    $query->bindParam(':email', $newUserData['email'], PDO::PARAM_STR); 
    $query->bindParam(':passw', $hashedPassword, PDO::PARAM_STR);
    $query->bindParam(':firstName', $newUserData['firstName'], PDO::PARAM_STR); 
    $query->bindParam(':lastName1', $newUserData['lastName1'], PDO::PARAM_STR); 
    $query->bindParam(':lastName2', $newUserData['lastName2'], PDO::PARAM_STR); 
    $query->bindParam(':userName', $newUserData['userName'], PDO::PARAM_STR); 
    $query->bindParam(':birthDate', $newUserData['birthDate'], PDO::PARAM_STR); 
    $query->bindParam(':orientation', $newUserData['orientation'], PDO::PARAM_STR); 
    $query->bindParam(':gender', $newUserData['gender'], PDO::PARAM_STR); 
    $query->bindParam(':longitude', $newUserData['longitude'], PDO::PARAM_STR);
    $query->bindParam(':latitude', $newUserData['latitude'], PDO::PARAM_STR);
    $query->bindParam(':bio', $newUserData['bio'], PDO::PARAM_STR); 
    $query->bindParam(':minAge', $newUserData['minAge'], PDO::PARAM_STR);
    $query->bindParam(':maxAge', $newUserData['maxAge'], PDO::PARAM_STR); 

    logServer("INSERT INTO User (Email, Pass, FirstName, LastName1, LastName2, Username, BirthDate, Orientation, Gender, Longitude, Latitude, Bio)
    VALUES ('". $newUserData['email']."', '". $hashedPassword ."', '". $newUserData['firstName']."', '". $newUserData['lastName1']."', '". $newUserData['lastName2']."', '". 
    $newUserData['userName']."', '". $newUserData['birthDate']."', '". $newUserData['orientation']."', '". $newUserData['gender']."', '". $newUserData['longitude']."', '". 
    $newUserData['latitude']."', '". $newUserData['bio']."', '". $newUserData['minAge']."', '".$newUserData['maxAge']."')");

    if ($query->execute()) {
        logServer("Usuario credo correctamente.");
        
    } else {
        echo "Error al actualizar los datos.";
        logServer("Error al crear el usuario.",'ERROR');

    }


    unset($pdo);
    unset($query);
}
?>
