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
recuperarUserDataDePrueba();

//var_dump($_SESSION['user_data']);
// Verificar si se ha realizado la solicitud AJAX de hacer update a la sesion para actualizar valores
if (isset($_POST['action']) && $_POST['action'] == 'update_session') {
    $_SESSION['user_data']['FirstName'] = $_POST['firstName'];
    $_SESSION['user_data']['LastName1'] = $_POST['lastName1'];
    $_SESSION['user_data']['LastName2'] = $_POST['lastName2'];
    $_SESSION['user_data']['Username'] = $_POST['userName'];
    $_SESSION['user_data']['BirthDate'] = $_POST['birthDate'];
    $_SESSION['user_data']['bio'] = $_POST['bio'];
    $_SESSION['user_data']['Gender'] = $_POST['gender'];
    $_SESSION['user_data']['Orientation'] = $_POST['orientation'];
    $_SESSION['user_data']['MinAge'] = $_POST['minAge'];
    $_SESSION['user_data']['MaxAge'] = $_POST['maxAge'];
    $_SESSION['user_data']['Longitude'] = $_POST['longitude'];
    $_SESSION['user_data']['Latitude'] = $_POST['latitude'];

    // Llamar a la función para actualizar en la base de datos
    updateUserData($_SESSION['user_data']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css" type="text/css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <title>Profile</title>

</head>
<body id="profileBody">
    <div class="container">
        <div class="top-header">
            <a class="back" href="/index.php"> < Atrás</a>
            <h1 class="profileTitle"> Perfil</h1>
        </div>    
        <div class="fieldsContainer">
            <form> 
<!-- User Info-->
            <div class ="field">
                <h3>First Name: </h3>
                <input type="text" id="firstName" value="<?php echo htmlspecialchars($_SESSION['user_data']['FirstName'])?>" required>
                </div>
                <!-- <small id="nombreError" style="color: red; display: none;">Name is required</small> -->
            <div class ="field">
                <h3>Last Names: </h3>
                <input type="text" id="lastName1" value="<?php echo htmlspecialchars($_SESSION['user_data']['LastName1'])?>" required>
                <input type="text" id="lastName2" value="<?php echo htmlspecialchars($_SESSION['user_data']['LastName2'])?>" required>
            </div>
            <div class ="field">
                <h3>UserName: </h3>
                <input type="text" id="userName" value="<?php echo htmlspecialchars($_SESSION['user_data']['Username'])?>" required>
                </div>
            <div class ="field">
                <h3>Birthday: </h3>
                <input type="text" id="birthdate" value="<?php echo htmlspecialchars($_SESSION['user_data']['BirthDate'])?>" required>
                </div>
            <div class ="field">
                <h3>Bio: </h3>
                <textarea id="bio" rows="10" cols ="50" required><?php echo htmlspecialchars($_SESSION['user_data']['bio'])?></textarea>
                </div>
            <div class ="field">
                <h3>User Location: </h3>
                <!-- API DE GOOGLE MAPS PARA ELEGIR COORDS (PRIMERO LATITUD Y DESPUES LONGITUD)-->
                <input type="text" id="latitude" value="<?php echo htmlspecialchars($_SESSION['user_data']['Latitude'])?>" required>
                <input type="text" id="longitude" value="<?php echo htmlspecialchars($_SESSION['user_data']['Longitude'])?>" required>
                <div id="map" style="height: 400px; width: 100%;"></div>
                 
            </div>
        
            <!-- User Preferences-->
            <div class ="field">
                <h3>Gender: </h3>
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
                    <input type="radio" name="gender" value="No Binario" <?php echo htmlspecialchars($_SESSION['user_data']['Gender'] == 'No binario') ? 'checked' : ''; ?> required>
                    No Binario
                </label>
            </div>
            <div class ="field">
                <h3>Orientation: </h3>
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
            <div class ="field">
                <h3>Age preferences: </h3>
                <label for="minAge">Edad Mínima: <span id="minAgeValue"><?php echo htmlspecialchars($_SESSION['user_data']['MinAge'])?></span></label>
                <label for="maxAge">Edad Máxima: <span id="maxAgeValue"><?php echo htmlspecialchars($_SESSION['user_data']['MaxAge'])?></span></label>

                <div class="range-slider">
                    <input type="range" id="minAge" min="18" max="99" value="<?php echo htmlspecialchars($_SESSION['user_data']['MinAge'])?>" oninput="updateRange()">
                    <input type="range" id="maxAge" min="18" max="99" value="<?php echo htmlspecialchars($_SESSION['user_data']['MaxAge'])?>" oninput="updateRange()">
                    <div class="progress"></div>
                </div>
            </div>

            </form>
        </div>

        <div class="bottom">
        <button class="saveButton" onclick="saveProfileChanges()">Save changes</button>
        <a class="toPhotoButton" href="/">Edit pictures</a>
        </div>
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
        function saveProfileChanges(){
            console.log("datos guardados!");
            
            //campos del form:
            const firstName = $('#firstName').val();
            const lastName1 = $('#lastName1').val();
            const lastName2 = $('#lastName2').val();
            const userName = $('#userName').val();
            const birthdate = $('#birthdate').val();
            const bio = $('#bio').val();
            const gender = $("input[name='gender']:checked").val(); //para que solo guarde la opcion seleccionada
            const orientation = $("input[name='orientacion']:checked").val(); //para que solo guarde la opcion seleccionada
            const minAge = $('#minAge').val();
            const maxAge = $('#maxAge').val();
            const latitude = $('#latitude').val();
            const longitude = $('#longitude').val();
            
            //envio de datos con AJAX
            $.ajax({
                url:'',
                type: 'POST',
                data: {
                    action: 'update_session',
                    firstName: firstName,
                    lastName1: lastName1,
                    lastName2: lastName2,
                    userName: userName,
                    birthDate: birthdate,
                    bio: bio,
                    gender: gender,
                    orientation: orientation,
                    minAge: minAge,
                    maxAge: maxAge,
                    latitude: latitude,
                    longitude: longitude
                },
                success: function(response){
                    console.log('datos actualizados correctamente');

                }, error: function(error){
                    console.error("Error al actualizar los datos:",error);
                }
            });
        }

        function updateRange() {
            //elementos del form 
            const minAge = document.getElementById('minAge');
            const maxAge = document.getElementById('maxAge');
            const minAgeValue = document.getElementById('minAgeValue');
            const maxAgeValue = document.getElementById('maxAgeValue');
            const progress = document.querySelector('.range-slider .progress');

            //la edad min no peude ser superior a la edad max (se cambia el valor)
            if (parseInt(minAge.value) > parseInt(maxAge.value)) {
            minAge.value = maxAge.value;
            }
            //se actualiza el valor de min y max en el form
            minAgeValue.textContent = minAge.value;
            maxAgeValue.textContent = maxAge.value;

            //barra de progreso
            progress.style.left = ((minAge.value - 18) * 100 / (100 - 18)) + '%';
            progress.style.width = ((maxAge.value - minAge.value) * 100 / (100 - 18)) + '%';
        }
        updateRange(); //Genera la progress bar
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo $_ENV['GOOGLE_MAPS_API_KEY']; ?>&callback=initMap&libraries=&v=weekly" async defer></script>
</body>
</html>
<?php
function recuperarUserDataDePrueba(){
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
                                    FirstName,
                                    LastName1,
                                    LastName2,
                                    Username,
                                    BirthDate,
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
    //print_r($_SESSION['user_data']);
    //eliminem els objectes per alliberar memòria 
    unset($pdo);
    unset($query);
}

function updateUserData($userData){
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

    // Preparamos la consulta para actualizar los datos
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
                                MaxAge = :maxAge, 
                                MinAge = :minAge
                            WHERE IdUser = :userId");
    // Asignamos los valores usando bindParam
    $query->bindParam(':firstName', $userData['FirstName'], PDO::PARAM_STR);
    $query->bindParam(':lastName1', $userData['LastName1'], PDO::PARAM_STR);
    $query->bindParam(':lastName2', $userData['LastName2'], PDO::PARAM_STR);
    $query->bindParam(':userName', $userData['Username'], PDO::PARAM_STR);
    $query->bindParam(':birthDate', $userData['BirthDate'], PDO::PARAM_STR);
    $query->bindParam(':orientation', $userData['Orientation'], PDO::PARAM_STR);
    $query->bindParam(':gender', $userData['Gender'], PDO::PARAM_STR);
    $query->bindParam(':longitude', $userData['Longitude'], PDO::PARAM_STR);
    $query->bindParam(':latitude', $userData['Latitude'], PDO::PARAM_STR);
    $query->bindParam(':maxAge', $userData['MaxAge'], PDO::PARAM_INT);
    $query->bindParam(':minAge', $userData['MinAge'], PDO::PARAM_INT);
    $query->bindParam(':userId', $userData['IdUser'], PDO::PARAM_INT);

    // Ejecutamos la consulta
    if ($query->execute()) {
        echo "Datos actualizados correctamente para el usuario con ID: " . $userData['IdUser'];
    } else {
        echo "Error al actualizar los datos.";
        print_r($query->errorInfo()); // Mostrar error en caso de fallo
    }

    print_r($_SESSION['user_data']);
    unset($pdo);
    unset($query);
}
?>