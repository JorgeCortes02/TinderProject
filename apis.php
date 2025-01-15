<?php
include 'config.php';

session_start();

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
function CalcAndOrderbyPosition()
{
    if (isset($_POST["indextToLoad"])) {
        // Tu ubicación (latitud y longitud)
        $myLat = (float) $_SESSION["user_data"]["Latitude"]; // Ejemplo de latitud (Nueva York)
        $myLong = (float) $_SESSION["user_data"]["Longitude"]; // Ejemplo de longitud (Nueva York)

        $indexToLoad = $_POST["indextToLoad"];

        $users = downloadUsersForDiscover($indexToLoad);
        $users = downloadFotos($users);

        // Calcular distancia de cada usuario respecto a tu ubicación
        foreach ($users as &$user) {

            $user["distance"] = calcularDistance((float) $user["Latitude"], (float) $user["Longitude"], $myLat, $myLong);

            $user["TotalPoints"] = CalcFinalPoints($user);



        }

        usort($users, function ($a, $b) {

            return $b["TotalPoints"] - $a["TotalPoints"];

        });

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
        exit;
    }

    // Suponiendo que $_SESSION['user_data']["IdUser"] contiene el valor del usuario
    $userId = $_SESSION['user_data']["IdUser"];
  
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
                        Points, 
                        UserAge
                    FROM User 
                    WHERE IdUser NOT IN (
                        SELECT LikedUserId 
                        FROM UserLikes 
                        WHERE UserId = :userId
                    )AND ((Orientation  = 'Heterosexual'
                    AND Gender = 'Mujer') or (Orientation  = 'Bisexual'
                    AND Gender = 'Mujer') )
                    AND IdUser != " . $_SESSION['user_data']['IdUser'] .
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
                        Points, 
                        UserAge
                    FROM User 
                    WHERE IdUser NOT IN (
                        SELECT LikedUserId 
                        FROM UserLikes 
                        WHERE UserId = :userId
                    )AND ((Orientation  = 'Homosexual'
                    AND Gender = 'Hombre') or (Orientation  = 'Bisexual'
                    AND Gender = 'Hombre') )
                   
                    AND IdUser != " . $_SESSION['user_data']['IdUser'] .
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
                        Points, 
                        UserAge
                    FROM User 
                    WHERE IdUser NOT IN (
                        SELECT LikedUserId 
                        FROM UserLikes 
                        WHERE UserId = :userId
                    )AND ((Orientation  = 'Heterosexual'
                    AND Gender = 'Hombre') or (Orientation  = 'Bisexual'
                    AND Gender = 'Hombre') )
                    AND IdUser != " . $_SESSION['user_data']['IdUser'] .
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
                        Points, 
                        UserAge
                    FROM User 
                    WHERE IdUser NOT IN (
                        SELECT LikedUserId 
                        FROM UserLikes 
                        WHERE UserId = :userId
                    )AND ((Orientation  = 'Homosexual'
                    AND Gender = 'Mujer') or (Orientation  = 'Bisexual'
                    AND Gender = 'Mujer') )
                    AND IdUser != " . $_SESSION['user_data']['IdUser'] .
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
                        Points, 
                        UserAge
                    FROM User 
                    WHERE IdUser NOT IN (
                        SELECT LikedUserId 
                        FROM UserLikes 
                        WHERE UserId = :userId
                    )AND ((Orientation  = 'Homosexual'
                    AND Gender = 'Mujer') or (Orientation  = 'Bisexual'
                    AND Gender = 'Hombre')or (Orientation  = 'Heterosexual'
                    AND Gender = 'Hombre') )
                    AND IdUser != " . $_SESSION['user_data']['IdUser'] .
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
                        Points, 
                        UserAge
                    FROM User 
                    WHERE IdUser NOT IN (
                        SELECT LikedUserId 
                        FROM UserLikes 
                        WHERE UserId = :userId
                    )AND ((Orientation  = 'Homosexual'
                    AND Gender = 'Hombre') or (Orientation  = 'Bisexual'
                    AND Gender = 'Mujer') or (Orientation  = 'Heterosexual'
                    AND Gender = 'Mujer') )
                    AND IdUser != " . $_SESSION['user_data']['IdUser'] .
            " AND IdUser >" . $indexToLoad . " LIMIT 50;"
        );
    }

    // Ejecutar la consulta con los parámetros correspondientes
    $query->execute([
        ':userId' => $userId
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
            exit;
        }

        $query = $pdo->prepare("SELECT URL FROM Photo where UserId = " . $user["IdUser"] . ";");
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
            exit;
        }

        try {
            echo "Comença la inserció<br>";
            //cadascun d'aquests interrogants serà substituit per un paràmetre.
            $stmt = $dbh->prepare("INSERT INTO UserLikes (UserId, LikedUserId) VALUES(?,?)");
            //a l'execució de la sentència li passem els paràmetres amb un array 
            $stmt->execute(array($_SESSION['user_data']['IdUser'], $likedUserID));
            echo "Insertat!";
        } catch (PDOException $e) {
            print "Error!: " . $e->getMessage() . " Desfem</br>";
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
            exit;
        }

        $query = $pdo->prepare("SELECT 1 FROM UserLikes where LikedUserId = " . $_SESSION['user_data']["IdUser"] . " AND UserId = " . $likedUserID . ";");
        $query->execute();
        $isaMatch = $query->fetchColumn();

        if ($isaMatch !== false) {
            saveANewMatch($likedUserID);
            $isaMatch = (int) $isaMatch;  // Convertir a entero
        } else {
            $isaMatch = 0;  // Si no hay resultado, devolver 0
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
        exit;
    }

    try {

        //cadascun d'aquests interrogants serà substituit per un paràmetre.
        $stmt = $dbh->prepare("INSERT INTO Matches (User1Id, User2Id) VALUES(?,?)");
        //a l'execució de la sentència li passem els paràmetres amb un array 
        $stmt->execute(array($_SESSION['user_data']['IdUser'], $userLiked));

    } catch (PDOException $e) {
        print "Error!: " . $e->getMessage() . " Desfem</br>";
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
            exit;
        }
    
        // Datos que quieres actualizar
        $id = $_SESSION["user_data"]["IdUser"]; // ID del usuario a actualizar
        $newPoints = $_SESSION["user_data"]["Points"]; // Nuevos puntos a actualizar
    
        // Preparar la consulta UPDATE
        $sql = "UPDATE User SET Points = :newPoints WHERE IdUser = :id";
    
        // Preparar la declaración
        $stmt = $pdo->prepare($sql);
    
        // Vincular los parámetros a las variables
        $stmt->bindParam(':newPoints', $newPoints, PDO::PARAM_INT); // Cambié a PDO::PARAM_INT
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    
        // Ejecutar la consulta
        if ($stmt->execute()) {
            echo "Usuario actualizado con éxito.";
        } else {
            echo "Error al actualizar el usuario.";
        }
    }
}




?> 