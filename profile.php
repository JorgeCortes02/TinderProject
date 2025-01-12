<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css" type="text/css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <title>Profile</title>
    <style>

    .range-slider {
      width: 300px;
      position: relative;
      height: 5px;
      background: #ddd;
      border-radius: 5px;
    }

    .range-slider input {
      position: absolute;
      width: 100%;
      pointer-events: none;
      -webkit-appearance: none;
      appearance: none;
      height: 5px;
      background: transparent;
    }

    .range-slider input::-webkit-slider-thumb {
      pointer-events: all;
      position: relative;
      z-index: 1;
      height: 15px;
      width: 15px;
      border-radius: 50%;
      background: purple;
      cursor: pointer;
      -webkit-appearance: none;
    }

    .range-slider input::-moz-range-thumb {
      pointer-events: all;
      position: relative;
      z-index: 1;
      height: 15px;
      width: 15px;
      border-radius: 50%;
      background: blue;
      cursor: pointer;
    }

    .range-slider .progress {
      position: absolute;
      height: 100%;
      background: blue;
      z-index: 0;
      border-radius: 5px;
    }

    .container{
        max-width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow-y: scroll;
    }
    .fieldsContainer{
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
  </style>
</head>

<?php 
session_start();
// Verificar si se ha realizado la solicitud AJAX de hacer update a la sesion para actualizar valores
if (isset($_POST['action']) && $_POST['action'] == 'update_session') {
    $_SESSION['firstName'] = $_POST['firstName'];
    $_SESSION['lastname1'] = $_POST['lastName1'];
    $_SESSION['lastname2'] = $_POST['lastName2'];
    $_SESSION['userName'] = $_POST['userName'];
    $_SESSION['birthday'] = $_POST['birthday'];
    $_SESSION['bio'] = $_POST['bio'];
    $_SESSION['gender'] = $_POST['gender'];
    $_SESSION['orientation'] = $_POST['orientation'];
    $_SESSION['minAge'] = $_POST['minAge'];
    $_SESSION['maxAge'] = $_POST['maxAge'];
    $_SESSION['longitude'] = $_POST['longitude'];
    $_SESSION['latitude'] = $_POST['latitude'];
}

//cargar variables de sesion al iniciar la pagina
$_SESSION['firstName'] = isset($_SESSION['firstName']) ? $_SESSION['firstName'] : 'Antonio';
$_SESSION['lastname1'] = isset($_SESSION['lastname1']) ? $_SESSION['lastname1'] : 'Polucion';
$_SESSION['lastname2'] = isset($_SESSION['lastname2']) ? $_SESSION['lastname2'] : 'Ortega';
$_SESSION['userName'] = isset($_SESSION['userName']) ? $_SESSION['userName'] : 'Antoneitor3000';
$_SESSION['birthday'] = isset($_SESSION['birthday']) ? $_SESSION['birthday'] : '1990-05-20';
$_SESSION['bio'] = isset($_SESSION['bio']) ? $_SESSION['bio'] : 'Hola soy Antiopnio y vengo en busca de gatitas...';
$_SESSION['gender'] = isset($_SESSION['gender']) ? $_SESSION['gender'] : 'Hombre';
$_SESSION['orientation'] = isset($_SESSION['orientation']) ? $_SESSION['orientation'] : 'Homosexual';
$_SESSION['minAge'] = isset($_SESSION['minAge']) ? $_SESSION['minAge'] : 18;
$_SESSION['maxAge'] = isset($_SESSION['maxAge']) ? $_SESSION['maxAge'] : 33;

$_SESSION['longitude'] = isset($_SESSION['longitude']) ? $_SESSION['longitude'] : -3.7038;
$_SESSION['latitude'] = isset($_SESSION['latitude']) ? $_SESSION['latitude'] : 40.4168;
//recuperarUserDataDePrueba(); //andamio para bd

?>
<body>
    <div class="container">
        <div class="top-header">
            <a class="back" href="/index.php"> < Back</a>
            <h1 class="profileTitle"> Profile</h1>
        </div>    
        <div class="fieldsContainer">
            <form> 
<!-- User Info-->
            <div class ="field">
                <h3>First Name: </h3>
                <input type="text" id="firstName" value="<?php echo htmlspecialchars($_SESSION['firstName'])?>" required>
                </div>
                <!-- <small id="nombreError" style="color: red; display: none;">Name is required</small> -->
            <div class ="field">
                <h3>Last Names: </h3>
                <input type="text" id="lastName1" value="<?php echo htmlspecialchars($_SESSION['lastname1'])?>" required>
                <input type="text" id="lastName2" value="<?php echo htmlspecialchars($_SESSION['lastname2'])?>" required>
            </div>
            <div class ="field">
                <h3>UserName: </h3>
                <input type="text" id="userName" value="<?php echo htmlspecialchars($_SESSION['userName'])?>" required>
                </div>
            <div class ="field">
                <h3>Birthday: </h3>
                <input type="text" id="birthday" value="<?php echo htmlspecialchars($_SESSION['birthday'])?>" required>
                </div>
            <div class ="field">
                <h3>Bio: </h3>
                <textarea id="bio" rows="10" cols ="50" required><?php echo htmlspecialchars($_SESSION['bio'])?></textarea>
                </div>
            <div class ="field">
                <h3>User Location: </h3>
                <!-- API DE GOOGLE MAPS PARA ELEGIR COORDS (PRIMERO LATITUD Y DESPUES LONGITUD)-->
                <input type="text" id="latitude" value="<?php echo htmlspecialchars($_SESSION['latitude'])?>" required>
                <input type="text" id="longitude" value="<?php echo htmlspecialchars($_SESSION['longitude'])?>" required>
                <div id="map" style="height: 400px; width: 100%;"></div>
                
                 
            </div>
        
            <!-- User Preferences-->
            <div class ="field">
                <h3>Gender: </h3>
                <label>
                    <input type="radio" name="gender" value="Hombre" <?php echo htmlspecialchars($_SESSION['gender'] == 'Hombre') ? 'checked' : ''; ?> required>
                    Hombre
                </label>
                </br>
                <label>
                    <input type="radio" name="gender" value="Mujer" <?php echo htmlspecialchars($_SESSION['gender'] == 'Mujer') ? 'checked' : ''; ?> required>
                    Mujer
                </label>
                </br>
                <label>
                    <input type="radio" name="gender" value="No Binario" <?php echo htmlspecialchars($_SESSION['gender'] == 'No binario') ? 'checked' : ''; ?> required>
                    No Binario
                </label>
            </div>
            <div class ="field">
                <h3>Orientation: </h3>
                <label>
                    <input type="radio" name="orientacion" value="Heterosexual" <?php echo htmlspecialchars($_SESSION['orientation'] == 'Heterosexual') ? 'checked' : ''; ?> required>
                    Heterosexual
                </label>
                </br>
                <label>
                    <input type="radio" name="orientacion" value="Homosexual" <?php echo htmlspecialchars($_SESSION['orientation'] == 'Homosexual') ? 'checked' : ''; ?> required>
                    Homosexual
                </label>
                </br>
                <label>
                    <input type="radio" name="orientacion" value="Bisexual" <?php echo htmlspecialchars($_SESSION['orientation'] == 'Bisexual') ? 'checked' : ''; ?> required>
                    Bisexual
                </label>
            </div>
            <div class ="field">
                <h3>Age preferences: </h3>
                <label for="minAge">Edad Mínima: <span id="minAgeValue"><?php echo htmlspecialchars($_SESSION['minAge'])?></span></label>
                <label for="maxAge">Edad Máxima: <span id="maxAgeValue"><?php echo htmlspecialchars($_SESSION['maxAge'])?></span></label>

                <div class="range-slider">
                    <input type="range" id="minAge" min="18" max="99" value="<?php echo htmlspecialchars($_SESSION['minAge'])?>" oninput="updateRange()">
                    <input type="range" id="maxAge" min="18" max="99" value="<?php echo htmlspecialchars($_SESSION['maxAge'])?>" oninput="updateRange()">
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
                lat: parseFloat(<?php echo $_SESSION['latitude']; ?>), 
                lng: parseFloat(<?php echo $_SESSION['longitude']; ?>) 
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
            const birthday = $('#birthday').val();
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
                    birthday: birthday,
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
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDotOWJULwqpIQ9VZr-W9oOH1c9nSb78OE&callback=initMap&libraries=&v=weekly" async defer></script>
</body>
</html>
<?php
function recuperarUserDataDePrueba(){
    try {
        $hostname = "localhost";
        $dbname = "DatingApp";
        $username = "root";
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