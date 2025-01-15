<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Función para cargar el archivo .env
function loadEnv($path) {
    if (!file_exists($path)) {
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


//andamio para pruebas
//recuperarUserDataDePrueba();
//var_dump($_SESSION['user_data']['FirstName']);
// Verificar si se ha realizado la solicitud AJAX de hacer update a la sesion para actualizar valores
if (isset($_POST['action']) && $_POST['action'] == 'update_session') {
    $_SESSION['user_data']['FirstName'] = $_POST['firstName'];
    $_SESSION['user_data']['LastName1'] = $_POST['lastName1'];
    $_SESSION['user_data']['LastName2'] = $_POST['lastName2'];
    $_SESSION['user_data']['Username'] = $_POST['userName'];
    $_SESSION['user_data']['BirthDate'] = $_POST['birthDate'];
    $_SESSION['user_data']['Bio'] = $_POST['bio'];
    $_SESSION['user_data']['Gender'] = $_POST['gender'];
    $_SESSION['user_data']['Orientation'] = $_POST['orientation'];
    $_SESSION['user_data']['Longitude'] = $_POST['longitude'];
    $_SESSION['user_data']['Latitude'] = $_POST['latitude'];

    // Llamar a la función para actualizar en la base de datos
    updateUserData($_SESSION['user_data']);
}

?>


<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="profile.js"></script>
    <title>Profile</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="styles.css">

</head>
<body>
    <div class="containerMessage">
        <!-- Encabezado -->
        <div class="header">
            <h1>IETINDER</h1>
            
        </div>

        <div class="fieldsContainer">
        <div class="error-message" id="errorMessage">Error: no se puede dejar un campo vacío</div>
            <div id = "scroll"></div>
        <form id="profileForm">

            <h2>Edita tu perfil</h2>
<!-- User Info-->
            <div class ="field">
                <h3>Nombre: </h3>
                <input type="text" id="firstName" value="<?php echo htmlspecialchars($_SESSION['user_data']['FirstName'])?>" required>
                </div>
                <!-- <small id="nombreError" style="color: red; display: none;">Name is required</small> -->
            <div class ="field">
                <h3>Apellidos: </h3>
                <input type="text" id="lastName1" value="<?php echo htmlspecialchars($_SESSION['user_data']['LastName1'])?>" required>
                <input type="text" id="lastName2" value="<?php echo htmlspecialchars($_SESSION['user_data']['LastName2'])?>" required>
            </div>
            <div class ="field">
                <h3>Alias: </h3>
                <input type="text" id="userName" value="<?php echo htmlspecialchars($_SESSION['user_data']['Username'])?>" required>
                </div>
            <div class ="field">
                <h3>Fecha de Nacimiento: </h3>
                <input type="text" id="birthdate" value="<?php echo htmlspecialchars($_SESSION['user_data']['BirthDate'])?>" required>
                </div>
            <div class ="field">
                <h3>Biografia: </h3>
                <textarea id="bio" rows="10" cols ="50" required><?php echo htmlspecialchars($_SESSION['user_data']['Bio'])?></textarea>
                </div>
            <div class ="field">
                <h3>Localización: </h3>
                <!-- API DE GOOGLE MAPS PARA ELEGIR COORDS (PRIMERO LATITUD Y DESPUES LONGITUD)-->
                <input type="text" id="latitude" value="<?php echo htmlspecialchars($_SESSION['user_data']['Latitude'])?>" required>
                <input type="text" id="longitude" value="<?php echo htmlspecialchars($_SESSION['user_data']['Longitude'])?>" required>
                <div id="map" style="height: 400px; width: 100%;"></div>
                 
            </div>
        
            <!-- User Preferences-->
            <div class ="field radioField">
                <h3>Género: </h3>
                <label>
                    <input type="radio" name="gender" value="Hombre" <?php echo htmlspecialchars($_SESSION['user_data']['Gender'] == 'Hombre') ? 'checked' : ''; ?> required>
                    Hombre
                </label>
                </br>
                <label>
                    <input type="radio" name="gender" value="Mujer" <?php echo htmlspecialchars($_SESSION['user_data']['Gender'] == 'Mujer') ? 'checked' : ''; ?> required>
                    Mujer
                </label>
                </br>
                <label>
                    <input type="radio" name="gender" value="No Binario" <?php echo htmlspecialchars($_SESSION['user_data']['Gender'] == 'No Binario') ? 'checked' : ''; ?> required>
                    No Binario
                </label>
            </div>
            <div class ="field radioField">
                <h3>Orientación: </h3>
                <label>
                    <input type="radio" name="orientacion" value="Heterosexual" <?php echo htmlspecialchars($_SESSION['user_data']['Orientation'] == 'Heterosexual') ? 'checked' : ''; ?> required>
                    Heterosexual
                </label>
                </br>
                <label>
                    <input type="radio" name="orientacion" value="Homosexual" <?php echo htmlspecialchars($_SESSION['user_data']['Orientation'] == 'Homosexual') ? 'checked' : ''; ?> required>
                    Homosexual
                </label>
                </br>
                <label>
                    <input type="radio" name="orientacion" value="Bisexual" <?php echo htmlspecialchars($_SESSION['user_data']['Orientation'] == 'Bisexual') ? 'checked' : ''; ?> required>
                    Bisexual
                </label>
            </div>
        
            <!-- <div class ="field">
                <h3>Preferencia de Edad: </h3>
                <label class="ageText" for="minAge">Edad Mínima: <span id="minAgeValue">$_SESSION['user_data']['MinAge'])?></span></label>
                </br>
                <label class="ageText" for="maxAge">Edad Máxima: <span id="maxAgeValue">$_SESSION['user_data']['MaxAge'])?></span></label>

                <div class="range-slider">
                    <input type="range" id="minAge" min="18" max="99" value=" echo htmlspecialchars($_SESSION['user_data']['MinAge'])?>" oninput="updateRange()">
                    <input type="range" id="maxAge" min="18" max="99" value=" echo htmlspecialchars($_SESSION['user_data']['MaxAge'])?>" oninput="updateRange()">
                    <div class="progress"></div>
                </div>
            </div> -->
            <div class="bottom">
            <button class="saveButton">Guardar</button>
            <a class="toPhotoButton" href="/">Editar Fotos</a>
            </div>
            </form>
        </div>


       

        <!-- Menú de navegación inferior -->
        <nav class="bottom-nav">
            <a href="discover.php">Descobrir</a>
            <a href="messages.php" class="active">Missatges</a>
            <a href="profile.php">Perfil</a>
        </nav>
    </div>
    <script>
        let map;
        let marker;
        function initMap() {
            const initialLocation = { 
                lat: parseFloat(<?php echo $_SESSION['user_data']['Latitude']; ?>), 
                lng: parseFloat(<?php echo $_SESSION['user_data']['Longitude']; ?>) 
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
        function updateRange() {
            // Elementos del formulario
            const minAge = document.getElementById('minAge');
            const maxAge = document.getElementById('maxAge');
            const minAgeValue = document.getElementById('minAgeValue');
            const maxAgeValue = document.getElementById('maxAgeValue');
            const progress = document.querySelector('.range-slider .progress');

            // La edad mínima no puede ser superior a la edad máxima (se cambia el valor)
            if (parseInt(minAge.value) > parseInt(maxAge.value)) {
                minAge.value = maxAge.value;
            }

            // Se actualizan los valores de edad mínima y máxima en el formulario
            minAgeValue.textContent = minAge.value;
            maxAgeValue.textContent = maxAge.value;

            // Ajuste de la barra de progreso
            const minValue = parseInt(minAge.value);
            const maxValue = parseInt(maxAge.value);
            const range = 81; // Rango total (99 - 18)
            progress.style.left = ((minValue - 18) / range) * 100 + '%';
            progress.style.width = ((maxValue - minValue) / range) * 100 + '%';
        }
        //updateRange(); //Genera la progress bar
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo $_ENV['GOOGLE_MAPS_API_KEY']; ?>&callback=initMap&libraries=&v=weekly" async defer></script>
</body>
</html>

<?php

function updateUserData($userData){
    try {
        global $username, $pw;
        $hostname = "localhost";
        $dbname = "DatingApp";
        $pdo = new PDO("mysql:host=$hostname;dbname=$dbname", "$username", "$pw");
    } catch (PDOException $e) {
        echo "Failed to get DB handle: " . $e->getMessage() . "\n";
        exit;
    }

    $query = $pdo->prepare("UPDATE User SET 
                                FirstName = :firstName,
                                LastName1 = :lastName1,
                                LastName2 = :lastName2,
                                Username = :userName,
                                BirthDate = :birthDate,
                                Orientation = :orientation, 
                                Gender = :gender, 
                                Longitude = :longitude, 
                                Latitude = :latitude, 
                                Bio = :bio
                            WHERE IdUser = :userId");
    // bindParam
    $query->bindParam(':firstName', $userData['FirstName'], PDO::PARAM_STR);
    $query->bindParam(':lastName1', $userData['LastName1'], PDO::PARAM_STR);
    $query->bindParam(':lastName2', $userData['LastName2'], PDO::PARAM_STR);
    $query->bindParam(':userName', $userData['Username'], PDO::PARAM_STR);
    $query->bindParam(':birthDate', $userData['BirthDate'], PDO::PARAM_STR);
    $query->bindParam(':orientation', $userData['Orientation'], PDO::PARAM_STR);
    $query->bindParam(':gender', $userData['Gender'], PDO::PARAM_STR);
    $query->bindParam(':longitude', $userData['Longitude'], PDO::PARAM_STR);
    $query->bindParam(':latitude', $userData['Latitude'], PDO::PARAM_STR);
    $query->bindParam(':userId', $userData['IdUser'], PDO::PARAM_INT);
    $query->bindParam(':bio', $userData['Bio'], PDO::PARAM_STR);

    if ($query->execute()) {
        echo "Datos actualizados correctamente para el usuario con ID: " . $userData['IdUser'];
    } else {
        echo "Error al actualizar los datos.";
        print_r($query->errorInfo());
    }

    print_r($_SESSION['user_data']);
    unset($pdo);
    unset($query);
}
?>
