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

if (isset($_GET['validate'])) {
    list($tokenMd5, $userId) = explode(':', trim($_GET['validate'])); // Separar el tokenMd5 y el userId
    //si no es int, levanta error
    if (!filter_var($userId, FILTER_VALIDATE_INT)) {
        logServer("ID de usuario no válido.", "ERROR");
        exit;
    }
    $originalToken = "IETI" . $userId . "TINDER";
    $expectedTokenMd5 = md5($originalToken);

    logServer("User Id recibido: " . $userId);
    logServer("Token recibido: " . $tokenMd5);
    logServer("Token esperado: " . $expectedTokenMd5);

    if ($tokenMd5 === $expectedTokenMd5) {
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
        
        logServer("Token de verificación coincide.");
        logServer("UPDATE User SET LoginAllowed = 1 WHERE IdUser =".$userId);
        $query = $pdo->prepare("UPDATE User SET LoginAllowed = 1 WHERE IdUser = :userId".";");
        $query->bindParam(':userId', $userId, PDO::PARAM_INT);
        
        if ($query->execute()) {
            logServer("Cuenta verificada con éxito.");
            $_SESSION['showVerificationNotification'] = true; //notificacion emergente
            header("Location: login.php");
        } else {
            logServer("Error al verificar la cuenta.", "ERROR");
            header("Location: register.php");
        }
    } else {
        logServer("Token de verificación inválido.", "ERROR");
    }
}

if (isset($_POST['action']) && $_POST['action'] == 'create_user') {
    // Recoger los campos del formulario
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
        <div class="error-photo" id="errorPhoto">
            <h3 class="error">Error: Se debe añadir al menos una foto</h3>
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
                <input type="date" id="birthdate" max ="<?php echo date('Y-m-d'); ?>"required>
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
                <h3>Fotografia: </h3>
                <div class ="photoField">
                    <!-- Aqui van un insert de la  primera foto-->
                    <section class="registerPhotoSection">
                        <div class="userPhoto" id="UserPhoto">
                            
                        </div>
                        <button type="button" class="addPhoto" onclick="document.getElementById('photoInput').click()">
                            <img src="images/anadir.png" alt="Add Photo" width="28" height="28">
                        </button>
                        <input type="file" id="photoInput" name="userImage" accept=".jpg, .jpeg, .png, .webp" style="display: none;">
                    </section>
                </div>
            </div>
            <div class="bottom">
            <button class="createProfileButton" id="createProfileButton">Crear Perfil</button>
            </div>
            </form>
        </div>
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
        logServer("Usuario creado correctamente.");
        $userId = $pdo -> lastInsertId();

        // Enviar el correo de verificación
        logServer("Enviando correo de verificación a :".$newUserData['email']);
        //sendVerificationEmail($userId, $newUserData['email']);
        insertUserPhoto($userId);
        
    } else {
        echo "Error al actualizar los datos.";
        logServer("Error al crear el usuario.",'ERROR');

    }


    unset($pdo);
    unset($query);
}

function insertUserPhoto($userId){
    $uploadDir = 'images/';

    if(isset($_FILES['userImage']) && $_FILES['userImage']['error'] === UPLOAD_ERR_OK){
        //tipos de archivo: jpg png webp jpeg
        $fileType = $_FILES['userImage']['type'];
        logServer("Tipo de archivo dado en campo imagen: " .$fileType);
        $validTypes = ['image/jpeg','image/png','image/jpg','image/webp'];

        if(!in_array($fileType , $validTypes)){
            logServer("Formato de archivo no válido","ERROR");
            return;
        }

        //Nombre del archivo, nombre unico en abse al user id
        $fileName = uniqid('user_' . $userId . '_') . '.' . pathinfo($_FILES['userImage']['name'], PATHINFO_EXTENSION);
        $filePath = $uploadDir . $fileName;

            // Mueve el archivo a la carpeta images
        if (move_uploaded_file($_FILES['userImage']['tmp_name'], $filePath)) {
            // Si el archivo se subió correctamente, inserta la ruta en la base de datos
            try {
                global $username, $pw;
                $hostname = "localhost";
                $dbname = "DatingApp";
                $pdo = new PDO("mysql:host=$hostname;dbname=$dbname", "$username", "$pw");
                
                // Inserta la ruta de la foto en la tabla photo
                $query = $pdo->prepare("INSERT INTO photo (UserId, URL) VALUES (:userId, :photoURL)");
                $query->bindParam(':userId', $userId, PDO::PARAM_INT);
                $query->bindParam(':photoURL', $filePath, PDO::PARAM_STR);
                
                logServer("INSERT INTO photo (UserId, URL) VALUES (".$userId.", ".$filePath.")");

                if ($query->execute()) {
                    logServer("Foto de perfil añadida correctamente para el usuario ID: $userId.");
                } else {
                    logServer("Error al guardar la foto de perfil para el usuario ID: $userId.", 'ERROR');
                }
            }catch (PDOException $e) {
                logServer("Error al conectar con la base de datos para guardar la foto: " . $e->getMessage(), 'ERROR');
            }
        } else {
            logServer("Error al mover el archivo de la foto de perfil.", 'ERROR');
        }
    } else {

        logServer("No se ha subido ningún archivo o ha ocurrido un error con la carga.", 'ERROR');
    }
}

function sendVerificationEmail($userId, $userEmail) {
    // Generar un token único para la verificación
    $token = "IETI" . $userId . "TINDER";
    $tokenMd5 = md5($token);  //  MD5 parámetro GET
    $tokenWithUserId = $tokenMd5 . ':' . $userId; // Concatenar el userId
    
    // Crear el enlace de verificación
    // $verificationLink = "http://localhost:8080/register.php?validate=" . $tokenMd5;
    //$verificationLink = "http://localhost:3000/register.php?validate=" . $tokenMd5;
    $verificationLink = "https://tinder2.ieti.site/register.php?validate=" . $tokenWithUserId;

    // Cuerpo del correo con estilo
    $subject = "Verifica tu cuenta en IETINDER";
    $message = '
    <html>
    <head>
    </head>
    <body style="font-family: Arial, sans-serif; background-color: #F4F4F4; color: #292929; padding: 20px;">
        <div class="container" style="background-color: #FFFFFF; padding: 20px; border-radius: 8px; text-align: center; max-width: 600px; margin: 0 auto;">
            <div class="header" style="background-color: #534FF6; color: #FFFFFF; padding: 15px; font-size: 24px; border-radius: 8px 8px 0 0;">
                <h1>¡Bienvenido a IETINDER!</h1>
            </div>
            <p>Gracias por registrarte. Haz clic en el siguiente botón para verificar tu cuenta:</p>
            <a href="'.$verificationLink.'" class="button" style="background-color: #4CAF50; color: #FFFFFF; padding: 15px 25px; font-size: 16px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 20px;">Verificar cuenta</a>
            <div class="footer" style="margin-top: 30px; color: #8B8EF9; font-size: 14px;">
                <p>Este enlace expirará en 48 horas.</p>
                <p>Si no solicitaste esta verificación, por favor ignora este correo.</p>
            </div>
        </div>
    </body>
    </html>
    ';
    // Headers del correo
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: no-reply@tinder2.ieti.site\r\n";
    //$headers .= "Reply-To: no-reply@tinder2.ieti.site\r\n";
    
    logServer(" email: ".$userEmail."\nSubject:".$subject."\nmessage: ".$message. "\nheaders: ".$headers);
    // Enviar el correo
    if (mail($userEmail, $subject, $message, $headers)) {
        echo "Correo de verificación enviado con éxito.";
        logServer("Correo enviado de forma correcta.");
    } else {
        echo "Error al enviar el correo de verificación.";
        logServer("Error al enviar el correo de verificación.","ERROR");
    }
}

?>
