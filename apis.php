<?php
// Incluir el autoload de Composer
require 'vendor/autoload.php'; 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_GET["api"])) {

    $apiSelected = $_GET["api"];

    switch ($apiSelected) {

        case "downdloadProfiles":

            CalcAndOrderbyPosition();
            break;


        case "insertNewLike":
            saveNewLike();
            break;
        case "isAMatch":
            isAMatch();
            break;
        case "sumPoints":
            sumAndUpdateUserPoints();
            break;
        case "logEvent":
            $input = json_decode(file_get_contents('php://input'), true); // Decodificar JSON
            if (isset($input["mensajeLog"]) && isset($input["tipoLog"]) && isset($input["pagLog"])) {
                logEvent($input["mensajeLog"], $input["pagLog"],$input["tipoLog"]);
            } 
            break;

        case "informationProfile":
            downloadInformForChat();
            break;

        case "getMessages":
            downloadMissages();
            break;

        case "saveNewMessage":
            saveNewMessage();
            break;
        case "downloadLastMessage":
            downloadLastMessage();
            break;
        case "uploadNewDisMaxAndMinorAge":
            sumAndUpdateUserMinMaxAgeAndDistance();
            break;
        

        case "downloadProfileWithFoto":
            downloadProfileWithFoto();
            break;
        
        case "getUserID":
            getUserID();
            break;

        case "downloadImages":
            downloadImages();
            break;
        
        case "deletePhoto":
            deletePhoto();
            break;

        case "uploadPhoto":
            uploadPhoto();
            break;
        case "destroySession":
                destroySession();
                break;
        
        case "generateLinkForChangePass":
            generateLinkForChangePass();
            break;

        case "saveANewPass":
            saveANewPass();
            break;
        
        case "softDeleteAccount":
            softDeleteAccount();
            break;
    }
}

/*
--Download, calc and order by distance for discover.

Las siguientes funciones son las necesarias para la descarga de datos para el discover.
Tenemos la que se encarga de calcular las distancias entre los dos puntos y devolverlas.
La función general que se encarga de llamar a la función que nos descarga los datos de la BBDD siguiendo los criterios de busqueda.
Y por último tenemos la función que se encarga de descargar los datos de la BBDD.

*/

// Función para calcular la distancia entre dos ubicaciones (en km); 



//Esta función llama a la función que nos devuelve los datos de los perfiles de la BBDD, 
//llama a la función que calcula la dustancia entre nosotros y el otro usuario y devuelve la lista de usuarios ordenados por distancia.
function calcularDistance($lat1, $lon1, $lat2, $lon2)
{
    $radioTierra = 6371; // Radio de la Tierra en kilómetros

    // Convertir las coordenadas de grados a radianes
    $lat1 = deg2rad($lat1);
    $lon1 = deg2rad($lon1);
    $lat2 = deg2rad($lat2);
    $lon2 = deg2rad($lon2);

    // Diferencias de latitud y longitud
    $deltaLat = $lat2 - $lat1;
    $deltaLon = $lon2 - $lon1;

    // Fórmula de Haversine
    $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
        cos($lat1) * cos($lat2) *
        sin($deltaLon / 2) * sin($deltaLon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    // Distancia en kilómetros
    $distancia = $radioTierra * $c;

    return $distancia; // Retorna la distancia en km
}

//Esta función llama a la función que nos devuelve los datos de los perfiles de la BBDD, 
//llama a la función que calcula la dustancia entre nosotros y el otro usuario y devuelve la lista de usuarios ordenados por distancia.
//Esta función llama a la función que nos devuelve los datos de los perfiles de la BBDD, 
//llama a la función que calcula la dustancia entre nosotros y el otro usuario y devuelve la lista de usuarios ordenados por distancia.
function CalcAndOrderbyPosition()
{
    if (isset($_POST["indextToLoad"])) {
        // Tu ubicación (latitud y longitud)
        $myLat = (float) $_SESSION["user_data"]["Latitude"];
        $myLong = (float) $_SESSION["user_data"]["Longitude"];
    
        $indexToLoad = $_POST["indextToLoad"];
    
        $users = downloadUsersForDiscover($indexToLoad);
        $users = downloadFotos($users);
        logServer("Calculando distancia de los usuarios respecto a la tuya...");
    
        // Calcular distancia de cada usuario respecto a tu ubicación
        foreach ($users as $key => &$user) { // Usar $key => &$user para acceder al índice
            $user["distance"] = calcularDistance((float) $user["Latitude"], (float) $user["Longitude"], $myLat, $myLong);

    
            if ($user["distance"] > $_SESSION["user_data"]["MaxDis"] || (int)$user["distance"] > $user["MaxDis"]) {
                unset($users[$key]); // Eliminar usuario del array
            } else {
                $user["TotalPoints"] = CalcFinalPoints($user);
                logServer("- User:" . $user["IdUser"] . " Distancia:" . $user["distance"] . " Puntos:" . $user["TotalPoints"]);
            }

        }
    
        // Reindexar el array después de eliminar elementos
        $users = array_values($users);
    
        logServer("Ordenando usuarios por Puntuación...");
        usort($users, function ($a, $b) {
            return $b["TotalPoints"] - $a["TotalPoints"];
        });
    
        logServer("Se ha recuperado y ordenado a los usuarios que se mostrarán.");
        // Devolver los resultados como JSON
        header('Content-Type: application/json');
        echo json_encode($users); // Devuelve el array de usuarios como JSON
        exit;
    }

}


//Descargamos los perfiles a mostrar en discover siguiendo nuestros criterios.
function downloadUsersForDiscover($indexToLoad): array
{

    try {
        global $username, $pw;
        $hostname = "localhost";
        $dbname = "DatingApp";
        $pdo = new PDO("mysql:host=$hostname;dbname=$dbname", "$username", "$pw");
    } catch (PDOException $e) {
        echo "Failed to get DB handle: " . $e->getMessage() . "\n";
        logServer("Error al conectar a la BBDD. Failed to get DB handle:". $e->getMessage(), "ERROR");
        exit;
    }

    // Suponiendo que $_SESSION['user_data']["IdUser"] contiene el valor del usuario
    $userId = $_SESSION['user_data']["IdUser"];
    $maxAge = $_SESSION['user_data']["MaxAge"];
    $minAge = $_SESSION['user_data']["MinAge"];
  
    // Preparar la consulta de manera segura usando un marcador de posición para :userId

    //Query para buscar perfiles en caso de que el usuario loggeado sea hombre hetero
    if ($_SESSION['user_data']["Gender"] == "Hombre" && $_SESSION['user_data']["Orientation"] == "Heterosexual") {

        $query = $pdo->prepare(
           "SELECT 
            IdUser, 
            Username, 
            Orientation, 
            Gender, 
            Longitude, 
            Latitude, 
            MaxAge, 
            MinAge,
            Points, 
            UserAge,
            MaxDis
        FROM User 
        WHERE IdUser NOT IN (
            SELECT LikedUserId 
            FROM UserLikes 
            WHERE UserId = :userId
        ) 
        AND Orientation  = 'Heterosexual'
        AND Gender = 'Mujer'
        AND UserAge <= :MaxAge 
                     AND UserAge >= :MinAge
                      AND UserAge <= :MaxAge
                      AND MaxAge >= " . $_SESSION['user_data']['UserAge']  . 
                     " AND MinAge <= " . $_SESSION['user_data']['UserAge']  .
                    " AND IdUser != " . $_SESSION['user_data']['IdUser'] .
            " AND IdUser >" . $indexToLoad . " LIMIT 50;"
        
        );
        //Query para buscar perfiles en caso de que el usuario loggeado sea hombre homo
    } else if ($_SESSION['user_data']["Gender"] == "Hombre" && $_SESSION['user_data']["Orientation"] == "Homosexual") {

        $query = $pdo->prepare(
            "SELECT 
                      IdUser, 
            Username, 
            Orientation, 
            Gender, 
            Longitude, 
            Latitude, 
            MaxAge, 
            MinAge,
            Points, 
            UserAge,
            MaxDis
                    FROM User 
                    WHERE IdUser NOT IN (
                        SELECT LikedUserId 
                        FROM UserLikes 
                        WHERE UserId = :userId
                    )AND ((Orientation  = 'Homosexual'
                    AND Gender = 'Hombre') or (Orientation  = 'Bisexual'
                    AND Gender = 'Hombre') )
                   AND UserAge <= :MaxAge 
                     AND UserAge >= :MinAge
                      AND MaxAge >= " . $_SESSION['user_data']['UserAge']  . 
                     " AND MinAge <= " . $_SESSION['user_data']['UserAge']  .
                    " AND IdUser != " . $_SESSION['user_data']['IdUser'] .
            " AND IdUser >" . $indexToLoad . " LIMIT 50;"
        );

        //Query para buscar perfiles en caso de que el usuario loggeado sea mujer hetero
    } else if ($_SESSION['user_data']["Gender"] == "Mujer" && $_SESSION['user_data']["Orientation"] == "Heterosexual") {

        $query = $pdo->prepare(
            "SELECT 
                        IdUser, 
            Username, 
            Orientation, 
            Gender, 
            Longitude, 
            Latitude, 
            MaxAge, 
            MinAge,
            Points, 
            UserAge,
            MaxDis
                    FROM User 
                    WHERE IdUser NOT IN (
                        SELECT LikedUserId 
                        FROM UserLikes 
                        WHERE UserId = :userId
                    )AND ((Orientation  = 'Heterosexual'
                    AND Gender = 'Hombre') or (Orientation  = 'Bisexual'
                    AND Gender = 'Hombre') )
                     AND UserAge <= :MaxAge 
                     AND UserAge >= :MinAge
                      AND MaxAge >= " . $_SESSION['user_data']['UserAge']  . 
                     " AND MinAge <= " . $_SESSION['user_data']['UserAge']  .
                    " AND IdUser != " . $_SESSION['user_data']['IdUser'] .
            " AND IdUser >" . $indexToLoad . " LIMIT 50;"
        );
        //Query para buscar perfiles en caso de que el usuario loggeado sea mujer homo
    } else if ($_SESSION['user_data']["Gender"] == "Mujer" && $_SESSION['user_data']["Orientation"] == "Homosexual") {

        $query = $pdo->prepare(
            "SELECT 
                     IdUser, 
            Username, 
            Orientation, 
            Gender, 
            Longitude, 
            Latitude, 
            MaxAge, 
            MinAge,
            Points, 
            UserAge,
            MaxDis
                    FROM User 
                    WHERE IdUser NOT IN (
                        SELECT LikedUserId 
                        FROM UserLikes 
                        WHERE UserId = :userId
                    )AND ((Orientation  = 'Homosexual'
                    AND Gender = 'Mujer') or (Orientation  = 'Bisexual'
                    AND Gender = 'Mujer') )
                    AND UserAge <= :MaxAge 
                     AND UserAge >= :MinAge
                      AND MaxAge >= " . $_SESSION['user_data']['UserAge']  . 
                     " AND MinAge <= " . $_SESSION['user_data']['UserAge']  .
                    " AND IdUser != " . $_SESSION['user_data']['IdUser'] .
            " AND IdUser >" . $indexToLoad . " LIMIT 50;"
            
        );

    }else if ($_SESSION['user_data']["Gender"] == "Mujer" && $_SESSION['user_data']["Orientation"] == "Bisexual") {

        $query = $pdo->prepare(
            "SELECT 
                     IdUser, 
            Username, 
            Orientation, 
            Gender, 
            Longitude, 
            Latitude, 
            MaxAge, 
            MinAge,
            Points, 
            UserAge,
            MaxDis
                    FROM User 
                    WHERE IdUser NOT IN (
                        SELECT LikedUserId 
                        FROM UserLikes 
                        WHERE UserId = :userId
                    )AND ((Orientation  = 'Homosexual'
                    AND Gender = 'Mujer') or (Orientation  = 'Bisexual'
                    AND Gender = 'Hombre')or (Orientation  = 'Heterosexual'
                    AND Gender = 'Hombre') )
                    AND UserAge <= :MaxAge 
                     AND UserAge >= :MinAge
                      AND MaxAge >= " . $_SESSION['user_data']['UserAge']  . 
                     " AND MinAge <= " . $_SESSION['user_data']['UserAge']  .
                    " AND IdUser != " . $_SESSION['user_data']['IdUser'] .
            " AND IdUser >" . $indexToLoad . " LIMIT 50;"
        );
    }else if ($_SESSION['user_data']["Gender"] == "Hombre" && $_SESSION['user_data']["Orientation"] == "Bisexual") {

        $query = $pdo->prepare(
            "SELECT 
                       IdUser, 
            Username, 
            Orientation, 
            Gender, 
            Longitude, 
            Latitude, 
            MaxAge, 
            MinAge,
            Points, 
            UserAge,
            MaxDis
                    FROM User 
                    WHERE IdUser NOT IN (
                        SELECT LikedUserId 
                        FROM UserLikes 
                        WHERE UserId = :userId
                    )AND ((Orientation  = 'Homosexual'
                    AND Gender = 'Hombre') or (Orientation  = 'Bisexual'
                    AND Gender = 'Mujer') or (Orientation  = 'Heterosexual'
                    AND Gender = 'Mujer') )
                    AND UserAge <= :MaxAge 
                     AND UserAge >= :MinAge
                      AND MaxAge >= " . $_SESSION['user_data']['UserAge']  . 
                     " AND MinAge <= " . $_SESSION['user_data']['UserAge']  .
                    " AND IdUser != " . $_SESSION['user_data']['IdUser'] .
            " AND IdUser >" . $indexToLoad . " LIMIT 50;"
        );
    }

    // Ejecutar la consulta con los parámetros correspondientes
    $query->execute([
        ':userId' => $userId,
        ':MaxAge' => $maxAge,
        ':MinAge' => $minAge
    ]);
    $users = $query->fetchAll(PDO::FETCH_ASSOC);
    return $users;
}


function downloadFotos($userDiccionari)
{

    foreach ($userDiccionari as &$user) {

        try {
            global $username, $pw;
            $hostname = "localhost";
            $dbname = "DatingApp";
            $pdo = new PDO("mysql:host=$hostname;dbname=$dbname", "$username", "$pw");
        } catch (PDOException $e) {
            echo "Failed to get DB handle: " . $e->getMessage() . "\n";
            logServer("Failed to get DB handle: " . $e->getMessage(),'ERROR');
            exit;
        }

        $query = $pdo->prepare("SELECT URL FROM Photo where UserId = " . $user["IdUser"] . ";");
        logServer("SELECT URL FROM Photo where UserId = " . $user["IdUser"] . ";");
        $query->execute();
        $photos = $query->fetchAll(PDO::FETCH_COLUMN);

        $i = 0;
        foreach ($photos as $img) {

            $user["img" . $i] = $img;
            $i++;

        }

    }

    return $userDiccionari;

}

/*Function Save Likes 

Aquí se encuentra la funcion mediante la cual guardaremos en la BBDD los likes que demos.

*/

function saveNewLIke()
{

    if (isset($_POST["likedUserId"])) {

        $likedUserID = $_POST["likedUserId"];

        try {
            global $username, $pw;
            $hostname = "localhost";
            $dbname = "DatingApp";
            $dbh = new PDO("mysql:host=$hostname;dbname=$dbname", "$username", "$pw");
        } catch (PDOException $e) {
            echo "Failed to get DB handle: " . $e->getMessage() . "\n";
            logServer("Failed to get DB handle: " . $e->getMessage(),'ERROR');

            exit;
        }

        try {
            echo "Comença la inserció<br>";
            //cadascun d'aquests interrogants serà substituit per un paràmetre.
            $stmt = $dbh->prepare("INSERT INTO UserLikes (UserId, LikedUserId) VALUES(?,?)");
            //a l'execució de la sentència li passem els paràmetres amb un array 
            $stmt->execute(array($_SESSION['user_data']['IdUser'], $likedUserID));
            echo "Insertat!";
            logServer("Like insertado correctamente.");
        } catch (PDOException $e) {
            print "Error!: " . $e->getMessage() . " Desfem</br>";

            logServer("Error al insertar like: " . $e->getMessage(),'ERROR');
        }
    }
}

function isAMatch()
{

    if (isset($_POST["likedUserId"])) {

        $likedUserID = $_POST["likedUserId"];

        try {
            global $username, $pw;
            $hostname = "localhost";
            $dbname = "DatingApp";
            $pdo = new PDO("mysql:host=$hostname;dbname=$dbname", "$username", "$pw");
        } catch (PDOException $e) {
            echo "Failed to get DB handle: " . $e->getMessage() . "\n";
            logServer("Failed to get DB handle: " . $e->getMessage(),'ERROR');
            exit;
        }

        $query = $pdo->prepare("SELECT 1 FROM UserLikes where LikedUserId = " . $_SESSION['user_data']["IdUser"] . " AND UserId = " . $likedUserID . ";");
        logServer("SELECT 1 FROM UserLikes where LikedUserId = " . $_SESSION['user_data']["IdUser"] . " AND UserId = " . $likedUserID . ";");
        $query->execute();
        $isaMatch = $query->fetchColumn();

        if ($isaMatch !== false) {
            saveANewMatch($likedUserID);
            $isaMatch = (int) $isaMatch;  // Convertir a entero
        } else {
            $isaMatch = 0;  // Si no hay resultado, devolver 0
            logServer("No se ha producido match");
        }

        echo json_encode($isaMatch);
        exit;

    }

}

function saveANewMatch($userLiked)
{

    try {
        global $username, $pw;
        $hostname = "localhost";
        $dbname = "DatingApp";
        $dbh = new PDO("mysql:host=$hostname;dbname=$dbname", "$username", "$pw");
    } catch (PDOException $e) {
        echo "Failed to get DB handle: " . $e->getMessage() . "\n";

        logServer("Failed to get DB handle: " . $e->getMessage(),'ERROR');
        exit;
    }

    try {

        //cadascun d'aquests interrogants serà substituit per un paràmetre.
        $stmt = $dbh->prepare("INSERT INTO Matches (User1Id, User2Id) VALUES(?,?)");
        logServer("INSERT INTO Matches (User1Id, User2Id) VALUES(?,?)");
        //a l'execució de la sentència li passem els paràmetres amb un array 
        $stmt->execute(array($_SESSION['user_data']['IdUser'], $userLiked));
        logServer("Se han insertado datos en Matches");

    } catch (PDOException $e) {
        print "Error!: " . $e->getMessage() . " Desfem</br>";
        logServer("Error: " . $e->getMessage(),'ERROR');
    }

}

function CalcFinalPoints($usuario)
{
    $finalDistancePoints = 0;
    $finalTotalPoints = 0;
    if($usuario["distance"] < 10){
        $finalDistancePoints += 10000;
    } else if($usuario["distance"] < 20 && $usuario["distance"] > 10){
        $finalDistancePoints += 7500;
    }else if($usuario["distance"] < 50 && $usuario["distance"] > 20){
        $finalDistancePoints += 5000;
    }else if($usuario["distance"] < 100 && $usuario["distance"] > 50){
        $finalDistancePoints += 2500;
    }else if( $usuario["distance"] > 100){
        $finalDistancePoints += 1000;
    }

    $finalTotalPoints = ((60 * $finalDistancePoints) /100) + (($usuario["Points"] * 40)/100);
    logServer("Puntos finales del usuario = ".$finalTotalPoints);
    return  $finalTotalPoints;

   
}

function sumAndUpdateUserPoints(){

    if(isset($_POST["points"])) {
        $_SESSION["user_data"]["Points"] += (int)$_POST["points"]; // Corrige la concatenación a la suma
        
        try {
            global $username, $pw;
            $hostname = "localhost";
            $dbname = "DatingApp";
            $pdo = new PDO("mysql:host=$hostname;dbname=$dbname", "$username", "$pw");
        } catch (PDOException $e) {
            echo "Failed to get DB handle: " . $e->getMessage() . "\n";
            logServer("Failed to get DB handle: " . $e->getMessage(),'ERROR');

            exit;
        }
    
        // Datos que quieres actualizar
        $id = $_SESSION["user_data"]["IdUser"]; // ID del usuario a actualizar
        $newPoints = $_SESSION["user_data"]["Points"]; // Nuevos puntos a actualizar
    
        // Preparar la consulta UPDATE
        $sql = "UPDATE User SET Points = :newPoints WHERE IdUser = :id";
        logServer("UPDATE User SET Points = :newPoints WHERE IdUser = :id");
    
        // Preparar la declaración
        $stmt = $pdo->prepare($sql);
    
        // Vincular los parámetros a las variables
        $stmt->bindParam(':newPoints', $newPoints, PDO::PARAM_INT); // Cambié a PDO::PARAM_INT
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    
        // Ejecutar la consulta
        if ($stmt->execute()) {
            echo "Usuario actualizado con éxito.";
            logServer("Usuario actualizado con éxito.");
        } else {
            echo "Error al actualizar el usuario.";
            logServer("Error al actualizar el usuario",'ERROR');
        }
    }
}

function logEvent($mensaje,$pag,$tipo = 'INFO'){

    $fecha = date('Y-m-d');
    $hora = date('H:i:s', time() + 3600);
    $userId = isset($_SESSION['user_data']['IdUser']) ? (string) $_SESSION['user_data']['IdUser']: 'Null';
    $directorio = __DIR__ . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR;

    //crear directorio si no existe:
    if(!file_exists($directorio)){
        mkdir($directorio, 0755, true);
    }

    $archivoLog="$directorio/$fecha.txt";
    //Formatear mensaje de PAGINA
    $mensajeFormateado = "[$hora] [$tipo] [$pag] [UserId=$userId]: $mensaje".PHP_EOL;

    file_put_contents($archivoLog, $mensajeFormateado, FILE_APPEND | LOCK_EX);

}

function logServer($mensaje, $tipo = 'INFO'){
    $pag = getFullUrl();
    logEvent($mensaje,  $pag,$tipo);
}

function getFullUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    $uri = $_SERVER['REQUEST_URI'];

    return $protocol . $host . $uri;
}


function sumAndUpdateUserMinMaxAgeAndDistance(){

    if(isset($_POST["maxAge"]) && isset($_POST["minAge"]) && isset($_POST["maxDistance"])) {
       
        
        try {
            global $username, $pw;
            $hostname = "localhost";
            $dbname = "DatingApp";
            $pdo = new PDO("mysql:host=$hostname;dbname=$dbname", "$username", "$pw");
        } catch (PDOException $e) {
            echo "Failed to get DB handle: " . $e->getMessage() . "\n";
            logServer("Error al conectar a la BBDD. Failed to get DB handle:". $e->getMessage(), "ERROR");
            exit;
        }
    
        // Datos que quieres actualizar
        $id = $_SESSION["user_data"]["IdUser"]; // ID del usuario a actualizar
        $_SESSION["user_data"]["MaxAge"] = $_POST["maxAge"];
        $_SESSION["user_data"]["MinAge"] = $_POST["minAge"];
        $_SESSION["user_data"]["MaxDis"] = $_POST["maxDistance"];
        print_r( $_SESSION["user_data"]);
        // Preparar la consulta UPDATE
        $sql = "UPDATE User 
        SET MaxAge = :maxAge, 
            MinAge = :minAge,
            MaxDis = :maxDis
        WHERE IdUser = :id";
    
        // Preparar la declaración
        $stmt = $pdo->prepare($sql);
    
  
        // Ejecutar la consulta
        if ($stmt->execute(
            [
            ':maxAge' => $_POST["maxAge"],
            ':minAge' => $_POST["minAge"],
            ':maxDis' => $_POST["maxDistance"],
            ':id' => $id
        ])) {
            echo "Usuario actualizado con éxito.";
            logServer("Usuario qactualizado con exito, se han añadido pintos");
        } else {
            echo "Error al actualizar los puntos del usuario.";
            logServer("Error al actualizar los puntos del usuario.", "ERROR");
        }
    }
}


function downloadInformForChat(){

        logServer("Cargando chats...");
        try {
            global $username, $pw;
            $hostname = "localhost";
            $dbname = "DatingApp";
            $pdo = new PDO("mysql:host=$hostname;dbname=$dbname", "$username", "$pw");
        } catch (PDOException $e) {
            echo "Failed to get DB handle: " . $e->getMessage() . "\n";
            logServer(" Failed to get DB handle:". $e->getMessage(), "ERROR");
            exit;
        }
    
        // Suponiendo que $_SESSION['user_data']["IdUser"] contiene el valor del usuario
        $userId = $_SESSION['user_data']["IdUser"];
        
        // Preparar la consulta de manera segura usando un marcador de posición para :userId
    
    
        $query = $pdo->prepare(
            "SELECT 
    CASE 
        WHEN m.user1Id = :userId THEN m.user2Id
        WHEN m.user2Id = :userId THEN m.user1Id
    END AS otherUser,
    u.Username
FROM Matches m
JOIN User u 
    ON u.IdUser = (
        CASE 
            WHEN m.user1Id = :userId THEN m.user2Id
            WHEN m.user2Id = :userId THEN m.user1Id
        END
    )
WHERE (m.user1Id = :userId OR m.user2Id = :userId)
  AND m.MatchId = :MatchId;"
);

    
        // Ejecutar la consulta con los parámetros correspondientes
        $query->execute([
            ':userId' => $userId,
            ':MatchId' => $_POST["matchId"]
        ]);
        $messageDiccionari = $query->fetchAll(PDO::FETCH_ASSOC);
      // Asegúrate de enviar un JSON válido como respuesta
header('Content-Type: application/json');  // Establece el tipo de contenido como JSON

// Si tienes un array como respuesta
echo json_encode($messageDiccionari);  // Convierte el array a JSON y lo imprime
       
    
    
    }



    function downloadMissages(){


        logServer("Cargando chats...");
        try {
            global $username, $pw;
            $hostname = "localhost";
            $dbname = "DatingApp";
            $pdo = new PDO("mysql:host=$hostname;dbname=$dbname", "$username", "$pw");
        } catch (PDOException $e) {
            echo "Failed to get DB handle: " . $e->getMessage() . "\n";
            logServer(" Failed to get DB handle:". $e->getMessage(), "ERROR");
            exit;
        }
    
        // Suponiendo que $_SESSION['user_data']["IdUser"] contiene el valor del usuario
        $userId = $_SESSION['user_data']["IdUser"];
        
        // Preparar la consulta de manera segura usando un marcador de posición para :userId
    
    
        $query = $pdo->prepare(
            "SELECT 
            MessageId,
            ReceiverUserId,
            SenderUserId,
            Text,
            SentAt
            FROM Message
            WHERE MatchId = :MatchId ORDER BY MessageId asc;"
);

    
        // Ejecutar la consulta con los parámetros correspondientes
        $query->execute([
           
            ':MatchId' => $_POST["matchId"]
        ]);
        $messageDiccionari = $query->fetchAll(PDO::FETCH_ASSOC);
      // Asegúrate de enviar un JSON válido como respuesta
header('Content-Type: application/json');  // Establece el tipo de contenido como JSON

// Si tienes un array como respuesta
echo json_encode($messageDiccionari);  // Convierte el array a JSON y lo imprime
       


    }

    function saveNewMessage()
    {
    
        if (isset($_POST["likedUserId"])) {
    
            $likedUserID = $_POST["likedUserId"];
            $matchId = $_POST["matchId"];
            $text = $_POST["Text"];
            try {
                global $username, $pw;
                $hostname = "localhost";
                $dbname = "DatingApp";
                $dbh = new PDO("mysql:host=$hostname;dbname=$dbname", "$username", "$pw");
            } catch (PDOException $e) {
                echo "Failed to get DB handle: " . $e->getMessage() . "\n";
                logServer("Failed to get DB handle: " . $e->getMessage(),'ERROR');
    
                exit;
            }
    
            try {
                echo "Comença la inserció<br>";
                //cadascun d'aquests interrogants serà substituit per un paràmetre.
                $stmt = $dbh->prepare("INSERT INTO Message (MatchId, SenderUserId, ReceiverUserId,Text) VALUES(?,?,?,?)");
                //a l'execució de la sentència li passem els paràmetres amb un array 
                $stmt->execute(array((int)$matchId, $_SESSION['user_data']['IdUser'], $likedUserID,$text));
                echo "Insertat!";
                logServer("Like insertado correctamente.");
            } catch (PDOException $e) {
                print "Error!: " . $e->getMessage() . " Desfem</br>";
    
                logServer("Error al insertar like: " . $e->getMessage(),'ERROR');
            }
        }
    }


    function downloadLastMessage(){



        logServer("Cargando chats...");
        try {
            global $username, $pw;
            $hostname = "localhost";
            $dbname = "DatingApp";
            $pdo = new PDO("mysql:host=$hostname;dbname=$dbname", "$username", "$pw");
        } catch (PDOException $e) {
            echo "Failed to get DB handle: " . $e->getMessage() . "\n";
            logServer(" Failed to get DB handle:". $e->getMessage(), "ERROR");
            exit;
        }
    
        // Suponiendo que $_SESSION['user_data']["IdUser"] contiene el valor del usuario
        $userId = $_SESSION['user_data']["IdUser"];
        
        // Preparar la consulta de manera segura usando un marcador de posición para :userId
    
        $query = $pdo->prepare(
            "SELECT 
                MessageId,
                ReceiverUserId,
                SenderUserId,
                Text,
                SentAt
            FROM Message
            WHERE MatchId = :MatchId
            AND SenderUserId = :sentUser
            AND MessageId > :lastMessageId
            ORDER BY MessageId ASC;"
        );
        
        // Ejecutar la consulta con los parámetros correspondientes
        $query->execute([
            ':MatchId' => $_POST["matchId"],
            ':sentUser' => $_POST["sentUser"],
            ':lastMessageId' => (int)$_POST["lastMessageId"] // Asegurando que sea un número
        ]);
        
        $messageDiccionari = $query->fetchAll(PDO::FETCH_ASSOC);
      // Asegúrate de enviar un JSON válido como respuesta
header('Content-Type: application/json');  // Establece el tipo de contenido como JSON

// Si tienes un array como respuesta
echo json_encode($messageDiccionari);  // Convierte el array a JSON y lo imprime
       


    }



    function downloadProfile(): array
{

    try {
        global $username, $pw;
        $hostname = "localhost";
        $dbname = "DatingApp";
        $pdo = new PDO("mysql:host=$hostname;dbname=$dbname", "$username", "$pw");
    } catch (PDOException $e) {
        echo "Failed to get DB handle: " . $e->getMessage() . "\n";
        logServer("Error al conectar a la BBDD. Failed to get DB handle:". $e->getMessage(), "ERROR");
        exit;
    }

    if($_POST["UserId"] == "x"){

        $userId = $_SESSION['user_data']["IdUser"];
    }else{
        $userId = (int)$_POST["UserId"];
    }
    
    // Preparar la consulta de manera segura usando un marcador de posición para :userId

    
        $query = $pdo->prepare(
           "SELECT 
            IdUser, 
            Username, 
            UserAge
            FROM User
            WHERE IdUser = :userId "
        );
      
    // Ejecutar la consulta con los parámetros correspondientes
    $query->execute([
       ':userId' =>  $userId
        
    ]);
    $users = $query->fetchAll(PDO::FETCH_ASSOC);
    return $users;
}

function downloadProfileWithFoto(){

    $users = downloadProfile();
    $users = downloadFotos($users);
     header('Content-Type: application/json');
        echo json_encode($users); // Devuelve el array de usuarios como JSON
        exit;
}




// Función para obtener el id del usuario
function getUserID(){
    if (isset($_SESSION['user_data']['IdUser'])) {
        echo json_encode(['userID' => $_SESSION['user_data']['IdUser']]);
    } else {
        echo json_encode(['error' => 'Usuario no autenticado']);
    }
}


// Función para cargar las fotos
function downloadImages(){

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $userID = $_POST['userID'];

        try {
            global $username, $pw;
            $hostname = "localhost";
            $dbname = "DatingApp";
            $pdo = new PDO("mysql:host=$hostname;dbname=$dbname", "$username", "$pw");
        } catch (PDOException $e) {
            echo "Failed to get DB handle: " . $e->getMessage() . "\n";
            logServer("Error al conectar a la BBDD. Failed to get DB handle: " . $e->getMessage(), "ERROR");
            exit;
        }

        $query = $pdo->prepare("SELECT URL FROM Photo where UserId = :id;");
        logServer("SELECT URL FROM Photo where UserId = " . $userID . ";");
        $query->bindParam(":id", $userID);
        $query->execute();
        $photos = $query->fetchAll(PDO::FETCH_COLUMN);

        $arrayImageUser = [];
        foreach ($photos as $img) {
            $arrayImageUser[] = $img;
        }

        // Devolver los resultados como JSON
        header('Content-Type: application/json');
        echo json_encode($arrayImageUser);
        exit;

    }
}


// Función para eliminar fotos
function deletePhoto(){
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $userId = $_POST['userID'];
        $imgSrc = $_POST['imgSrc'];

        try {
            global $username, $pw;
            $hostname = "localhost";
            $dbname = "DatingApp";
            $pdo = new PDO("mysql:host=$hostname;dbname=$dbname", "$username", "$pw");
        } catch (PDOException $e) {
            echo "Failed to get DB handle: " . $e->getMessage() . "\n";
            logServer("Error al conectar a la BBDD. Failed to get DB handle: " . $e->getMessage(), "ERROR");
            exit;
        }

        try {
            
            $query = $pdo->prepare("DELETE FROM Photo where URL= :url and UserId= :id;");
            logServer("DELETE FROM Photo where URL=". $userId ." and UserId=". $imgSrc . ";");
            $query->bindParam(":url", $imgSrc);
            $query->bindParam(":id", $userId);
            $query->execute();

            if ($query->rowCount() > 0) {
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Foto eliminada correctamente de la base de datos.'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'No se encontró la foto para eliminar.'
                ]);
            }

            // eliminar la foto del server
            $filePath = $_SERVER['DOCUMENT_ROOT']."/" . $imgSrc;
            if (file_exists($filePath)) {
                if (unlink($filePath)) {
                    logServer("Archivo eliminado localmente: " . $filePath, "INFO");
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'No se pudo eliminar el archivo local.'
                    ]);
                    exit;
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Archivo local no encontrado: ' . $filePath
                ]);
                exit;
            }


            exit;
        }

        catch (PDOException $e) {
            // Si ocurre un error durante la ejecución de la consulta
            echo json_encode([
                'success' => false,
                'message' => 'Error al ejecutar la consulta: ' . $e->getMessage()
            ]);
        }

    }
}


// Función para añadir fotos
function uploadPhoto() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // Verificar que se ha subido un archivo
        if (!isset($_FILES['file'])) {
            echo json_encode(['success' => false, 'message' => 'No se ha subido ningún archivo.']);
            logServer("No se ha subido ningún archivo.",'ERROR');
            exit; 
        }

        $userId = $_POST['userID'];
        $file = $_FILES['file'];

        // Validar si el archivo es válido
        $validExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($fileExtension, $validExtensions)) {
            echo json_encode(['success' => false, 'message' => 'Formato de archivo no permitido.']);
            logServer("Formato de archivo no permitido.",'ERROR');
            exit;
        }

        // Obtener el nombre original del archivo (sin la extensión)
        $originalFileName = pathinfo($file['name'], PATHINFO_FILENAME);

        // Limpiar caracteres no deseados del nombre original
        $cleanFileName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $originalFileName);

        // Directorio de subida
        $uploadDir = "images/";

        // Conectar a la base de datos
        try {
            global $username, $pw;
            $hostname = "localhost";
            $dbname = "DatingApp";
            $pdo = new PDO("mysql:host=$hostname;dbname=$dbname", "$username", "$pw");
        } catch (PDOException $e) {
            echo "Failed to get DB handle: " . $e->getMessage() . "\n";
            exit;
        }

        // Verificar si el nombre del archivo ya existe en la base de datos y ajustar si es necesario
        try {
            $suffix = 0;
            $uniqueFileName = $cleanFileName;
            do {
                // Si hay un sufijo, agregarlo al nombre del archivo
                if ($suffix > 0) {
                    $uniqueFileName = $cleanFileName . "_{$suffix}";
                }

                // Construir la ruta completa del archivo
                $filePath = $uploadDir . $uniqueFileName . "." . $fileExtension;

                // Consultar si ya existe un archivo con esta ruta en la base de datos
                $query = $pdo->prepare("SELECT COUNT(*) FROM Photo WHERE URL = :url");
                $query->bindParam(":url", $filePath, PDO::PARAM_STR);
                $query->execute();
                $exists = $query->fetchColumn() > 0;

                $suffix++;
            } while ($exists);

        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Error al verificar la existencia del archivo: ' . $e->getMessage()]);
            exit;
        }

        // Mover el archivo al directorio de imágenes
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            echo json_encode(['success' => false, 'message' => 'Error al mover el archivo al directorio.']);
            logServer("Error al mover el archivo al directorio.",'ERROR');
            exit;
        }

        // Guardar la URL de la foto en la base de datos
        try {
            $insertQuery = $pdo->prepare("INSERT INTO Photo (UserId, URL) VALUES (:userId, :url)");
            $insertQuery->bindParam(":userId", $userId, PDO::PARAM_INT);
            $insertQuery->bindParam(":url", $filePath, PDO::PARAM_STR);
            $insertQuery->execute();
            logServer("INSERT INTO Photo (UserId, URL) VALUES (".$userId.",".$filePath.");");

        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Error al guardar la URL en la base de datos: ' . $e->getMessage()]);
            exit;
        }

        // Devolver la respuesta de éxito
        echo json_encode(['success' => true, 'fileURL' => $filePath]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    }
}

function destroySession(){
    logServer("Eliminando sesión...");
    session_destroy();
    // Redirigir al usuario, por ejemplo, a la página de inicio de sesión
    $_SESSION['showCloseSessionNotification'] = true;
    header("Location: login.php");
    exit;
}

function getIDByMail($email){


    try {
        global $username, $pw;
        $hostname = "localhost";
        $dbname = "DatingApp";
        $pdo = new PDO("mysql:host=$hostname;dbname=$dbname", "$username", "$pw");
    } catch (PDOException $e) {
        echo "Failed to get DB handle: " . $e->getMessage() . "\n";
        logServer("Error al conectar a la BBDD. Failed to get DB handle:". $e->getMessage(), "ERROR");
        exit;
    }
   
    
    // Preparar la consulta de manera segura usando un marcador de posición para :userId

    
    $query = $pdo->prepare(
        "SELECT 
         IdUser
         FROM User
         WHERE Email = :Email"
     );
     
     // Ejecutar la consulta con los parámetros correspondientes
     $query->execute([
        ':Email' => $email
     ]);
     
     // Obtener solo el valor de la columna 'IdUser' de la primera fila
     $userId = $query->fetchColumn();
     
     return $userId;

    }

    function generateLinkForChangePass() {
        // Obtenemos el ID del usuario mediante su email
        $userId = getIDByMail($_POST["email"]);
    
        if ($userId === false) {
            // Si no se encuentra el usuario, devolvemos un mensaje de error
            echo "0";
        } else {
            // Si se encuentra el usuario, encriptamos el ID
            $encryptID = encrypt($userId, "grupo2Ietinder");
            $encryptMail = encrypt($_POST["email"], "grupo2Ietinder");

            $encrypt= $encryptID . ";" . $encryptMail;
            // Creamos el enlace de verificación
            $verificationLink = "https://tinder2.ieti.site/forgot_password.php?token=" . $encrypt;
    
            // Configuramos el mensaje de correo
            $subject = "Cambia tu contraseña en IETINDER";
            $message = '
            <html>
            <head>
            </head>
            <body style="font-family: Arial, sans-serif; background-color: #F4F4F4; color: #292929; padding: 20px;">
                <div class="container" style="background-color: #FFFFFF; padding: 20px; border-radius: 8px; text-align: center; max-width: 600px; margin: 0 auto;">
                    <div class="header" style="background-color: #534FF6; color: #FFFFFF; padding: 15px; font-size: 24px; border-radius: 8px 8px 0 0;">
                        <h1>¡Cambio de contraseña en IETINDER!</h1>
                    </div>
                    <p>Haz click en el siguiente botón para resetear tu contraseña:</p>
                    <a href="'.$verificationLink.'" class="button" style="background-color: #4CAF50; color: #FFFFFF; padding: 15px 25px; font-size: 16px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 20px;">Verificar cuenta</a>
                    <div class="footer" style="margin-top: 30px; color: #8B8EF9; font-size: 14px;">
                       <p>Si no solicitaste este cambio, por favor ignora este correo.</p>
                    </div>
                </div>
            </body>
            </html>
            ';
    
            // Configuración de PHPMailer
            $mail = new PHPMailer(true);
    
            try {
                // Configuración del servidor SMTP
                $mail->isSMTP();  
                $mail->Host = 'smtp.gmail.com';  // Cambia a tu servidor SMTP
                $mail->SMTPAuth = true;
                $mail->Username = 'jcortesblanquez.cf@iesesteveterradas.cat'; // Tu correo
                $mail->Password = 'wrhgoklxamuzrkrt';     // Tu contraseña (o contraseña de aplicación)
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;
    
                // Datos del correo
                $mail->setFrom('no-reply@tinder2.ieti.site', 'no-reply@tinder2.ieti.site'); // Dirección "De"
                $mail->addAddress($_POST['email'], 'Usuario');  // Dirección del destinatario
                $mail->Subject = $subject;
                $mail->Body    = $message;
                $mail->isHTML(true);  // Establecer el cuerpo del mensaje en formato HTML
    
                // Enviar el correo
                $mail->send();
                echo "1";  // Mensaje de éxito
            } catch (Exception $e) {
                echo "Error al enviar el correo de verificación: {$mail->ErrorInfo}";  // Mensaje de error
            }
        }
    }

    // Función para encriptar datos
function encrypt($data, $key) {
    // Generamos un IV (vector de inicialización) aleatorio
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));

    // Encriptamos el dato usando AES-256-CBC
    $encryptedData = openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv);

    // Retornamos los datos encriptados junto con el IV para poder desencriptarlos luego
    return base64_encode($encryptedData . '::' . $iv);
}

// Función para desencriptar datos
function decrypt($data, $key) {
    // Decodificamos los datos encriptados (el IV y los datos encriptados están concatenados)
    $decodedData = base64_decode($data);
    
    // Verificamos si la decodificación fue exitosa
    if ($decodedData === false) {
        return false;
    }

    // Intentamos separar los datos en el IV y los datos encriptados usando '::' como separador
    $parts = explode('::', $decodedData, 2);

    // Si no conseguimos dos partes, significa que el formato no es correcto
    if (count($parts) !== 2) {
        return false; // O puedes devolver un mensaje de error específico: "Error: Datos mal formateados."
    }

    // Extraemos el encryptedData y el IV
    list($encryptedData, $iv) = $parts;

    // Longitud correcta del IV para AES-256-CBC
    $ivLength = openssl_cipher_iv_length('aes-256-cbc');

    // Verificar si el IV es null o tiene la longitud incorrecta
    if ($iv === null || strlen($iv) !== $ivLength) {
        return false; // O puedes devolver un mensaje de error: "Error: IV tiene longitud incorrecta";
    }

    // Desencriptamos los datos usando AES-256-CBC y el IV
    $decrypted = openssl_decrypt($encryptedData, 'aes-256-cbc', $key, 0, $iv);

    // Si no se pudo desencriptar, devolvemos false
    if ($decrypted === false) {
        return false; // También puedes devolver otro tipo de mensaje si lo prefieres
    }

    return $decrypted;
}

function isAVlidToken($userId, $email){


try {
        global $username, $pw;
        $hostname = "localhost";
        $dbname = "DatingApp";
        $pdo = new PDO("mysql:host=$hostname;dbname=$dbname", "$username", "$pw");
    } catch (PDOException $e) {
        echo "Failed to get DB handle: " . $e->getMessage() . "\n";
        logServer("Error al conectar a la BBDD. Failed to get DB handle:". $e->getMessage(), "ERROR");
        exit;
    }
   
    
    // Preparar la consulta de manera segura usando un marcador de posición para :userId

    
    $query = $pdo->prepare(
        "SELECT 
         1
         FROM User
         WHERE Email = :Email
         AND IdUser = :isUser"
     );
     
     // Ejecutar la consulta con los parámetros correspondientes
     $query->execute([
        ':Email' => $email,
        ':isUser' => $userId
     ]);
     
     // Obtener solo el valor de la columna 'IdUser' de la primera fila
     $userId = $query->fetchColumn();
     
     return $userId;

}


function saveANewPass(){

   if(isset($_POST["newPass"])){

    $newPass = $_POST["newPass"];
  

   
    
    $hashedPassword = hash('sha256',  $newPass);
    
    try {
        global $username, $pw;
        $hostname = "localhost";
        $dbname = "DatingApp";
        $pdo = new PDO("mysql:host=$hostname;dbname=$dbname", "$username", "$pw");
    } catch (PDOException $e) {
        echo "Failed to get DB handle: " . $e->getMessage() . "\n";
        logServer("Failed to get DB handle: " . $e->getMessage(),'ERROR');

        exit;
    }

    // Datos que quieres actualizar
    $id = $_SESSION["user"]["id"]; // ID del usuario a actualizar
   

    // Preparar la consulta UPDATE
    $sql = "UPDATE User SET Password = :newPass WHERE IdUser = :id";
    logServer("UPDATE User SET Password = :newPass WHERE IdUser = :id");

    // Preparar la declaración
    $stmt = $pdo->prepare($sql);

    // Vincular los parámetros a las variables
    $stmt->bindParam(':newPass', $hashedPassword , PDO::PARAM_STR); // Cambié a PDO::PARAM_INT
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    // Ejecutar la consulta
    if ($stmt->execute()) {
        echo "Usuario actualizado con éxito.";
        logServer("Usuario actualizado con éxito.");
    } else {
        echo "Error al actualizar el usuario.";
        logServer("Error al actualizar el usuario",'ERROR');
    }
   }


}
function softDeleteAccount(){

    try {
        global $username, $pw;
        $hostname = "localhost";
        $dbname = "DatingApp";
        $pdo = new PDO("mysql:host=$hostname;dbname=$dbname", "$username", "$pw");
    } catch (PDOException $e) {
        echo "Failed to get DB handle: " . $e->getMessage() . "\n";
        logServer("Failed to get DB handle: " . $e->getMessage(),'ERROR');

        exit;
    }

    // Datos que quieres actualizar
    $id = $_SESSION["user_data"]["IdUser"]; // ID del usuario a actualizar
   

    // Preparar la consulta UPDATE
    $sql = "UPDATE User SET DeleteAccount = 1 WHERE IdUser = :id";
    logServer("UPDATE User SET DeleteAccount = 1 WHERE IdUser = :id");

    // Preparar la declaración
    $stmt = $pdo->prepare($sql);

    // Vincular los parámetros a las variables
    
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    // Ejecutar la consulta
    if ($stmt->execute()) {
        echo "Usuario actualizado con éxito.";
        logServer("Usuario actualizado con éxito.");
    } else {
        echo "Error al actualizar el usuario.";
        logServer("Error al actualizar el usuario",'ERROR');
    }
   }


?>