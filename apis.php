<?php
session_start();
// Establecemos datos de la sesión para el usuario
$_SESSION["user_data"]["Gender"] = "Mujer";
$_SESSION["user_data"]["Orientation"] = "Homosexual";

if (isset($_GET["api"])) {
    // Verificamos que se haya solicitado una API
    $apiSelected = $_GET["api"];

    switch ($apiSelected) {
        // Dependiendo de la API seleccionada, ejecutamos una función diferente
        case "downdloadProfiles":
            CalcAndOrderbyPosition();
            break;

        case "insertNewLike":
            saveNewLike();
            break;
        case "isAMatch":
            isAMatch();
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

// Esta función llama a la función que nos devuelve los datos de los perfiles de la BBDD, 
// llama a la función que calcula la distancia entre nosotros y el otro usuario y devuelve la lista de usuarios ordenados por distancia.
function CalcAndOrderbyPosition()
{
    if (isset($_POST["indextToLoad"])) {
        // Tu ubicación (latitud y longitud)
        $myLat = (float) $_SESSION["user_data"]["Latitude"]; // Ejemplo de latitud (Nueva York)
        $myLong = (float) $_SESSION["user_data"]["Longitude"]; // Ejemplo de longitud (Nueva York)

        $indexToLoad = $_POST["indextToLoad"];

        $users = downloadUsersForDiscover($indexToLoad); // Descargar los usuarios para el descubrimiento
        $users = downloadFotos($users); // Descargar fotos de los usuarios

        // Calcular distancia de cada usuario respecto a tu ubicación
        foreach ($users as &$user) {
            $user["distance"] = calcularDistance((float) $user["Latitude"], (float) $user["Longitude"], $myLat, $myLong);
        }

        usort($users, function ($a, $b) {
            return $a["distance"] - $b["distance"]; // Ordenar usuarios por distancia
        });

        // Devolver los resultados como JSON
        header('Content-Type: application/json');
        echo json_encode($users); // Devuelve el array de usuarios como JSON
        exit;
    }
}

// Descargamos los perfiles a mostrar en discover siguiendo nuestros criterios.
function downloadUsersForDiscover($indexToLoad): array
{
    try {
        // Conexión a la base de datos
        $hostname = "localhost";
        $dbname = "DatingApp";
        $username = "root";
        $pw = "1234";
        $pdo = new PDO("mysql:host=$hostname;dbname=$dbname", "$username", "$pw");
    } catch (PDOException $e) {
        echo "Failed to get DB handle: " . $e->getMessage() . "\n";
        exit;
    }

    // Suponiendo que $_SESSION['user_data']["IdUser"] contiene el valor del usuario
    $userId = $_SESSION['user_data']["IdUser"];
    $maxAge = $_SESSION['user_data']["MaxAge"];
    $minAge = $_SESSION['user_data']["MinAge"];

    // Consultas para buscar perfiles según el género y orientación del usuario logueado
    // Query para hombres heterosexuales
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
                        UserAge
                    FROM User 
                    WHERE IdUser NOT IN (
                        SELECT LikedUserId 
                        FROM UserLikes 
                        WHERE UserId = :userId
                    ) 
                    AND MaxAge <= :MaxAge 
                    AND MinAge >= :MinAge
                    AND Orientation  = 'Heterosexual'
                    AND Gender = 'Mujer'
                    AND IdUser != " . $_SESSION['user_data']['IdUser'] .
            " AND IdUser >" . $indexToLoad . " LIMIT 50;"
        );
    }
    // Query para hombres homosexuales
    else if ($_SESSION['user_data']["Gender"] == "Hombre" && $_SESSION['user_data']["Orientation"] == "Homosexual") {
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
                        UserAge
                    FROM User 
                    WHERE IdUser NOT IN (
                        SELECT LikedUserId 
                        FROM UserLikes 
                        WHERE UserId = :userId
                    ) 
                    AND MaxAge <= :MaxAge 
                    AND MinAge >= :MinAge
                    AND Orientation  = 'Homosexual'
                    AND Gender = 'Hombre' 
                    AND IdUser != " . $_SESSION['user_data']['IdUser'] .
            " AND IdUser >" . $indexToLoad . " LIMIT 50;"
        );
    }
    // Query para mujeres heterosexuales
    else if ($_SESSION['user_data']["Gender"] == "Mujer" && $_SESSION['user_data']["Orientation"] == "Heterosexual") {
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
                        UserAge
                    FROM User 
                    WHERE IdUser NOT IN (
                        SELECT LikedUserId 
                        FROM UserLikes 
                        WHERE UserId = :userId
                    ) 
                    AND MaxAge <= :MaxAge 
                    AND MinAge >= :MinAge
                    AND Orientation  = 'Heterosexual'
                    AND Gender = 'Hombre'
                    AND IdUser != " . $_SESSION['user_data']['IdUser'] .
            " AND IdUser >" . $indexToLoad . " LIMIT 50;"
        );
    }
    // Query para mujeres homosexuales
    else if ($_SESSION['user_data']["Gender"] == "Mujer" && $_SESSION['user_data']["Orientation"] == "Homosexual") {
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
                        UserAge
                    FROM User 
                    WHERE IdUser NOT IN (
                        SELECT LikedUserId 
                        FROM UserLikes 
                        WHERE UserId = :userId
                    ) 
                    AND MaxAge <= :MaxAge 
                    AND MinAge >= :MinAge
                    AND Orientation  = 'Homosexual'
                    AND Gender = 'Mujer'
                    AND IdUser != " . $_SESSION['user_data']['IdUser'] .
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

// Descargar las fotos asociadas a los perfiles de los usuarios.
function downloadFotos($userDiccionari)
{
    foreach ($userDiccionari as &$user) {
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

        // Consultar las fotos del usuario
        $query = $pdo->prepare("SELECT imagePath FROM ProfilePhotos WHERE userId = :userId;");
        $query->execute([':userId' => $user["IdUser"]]);
        $photos = $query->fetchAll(PDO::FETCH_ASSOC);

        $user["photos"] = $photos;
    }
    return $userDiccionari;
}





/*Function Save Likes 

Aquí se encuentra la funcion mediante la cual guardaremos en la BBDD los likes que demos.

*/

function saveNewLIke()
{
    // Comprobamos si se ha recibido el id del usuario al que se le dio el "like"
    if (isset($_POST["likedUserId"])) {

        $likedUserID = $_POST["likedUserId"];

        try {
            // Conexión a la base de datos
            $hostname = "localhost";
            $dbname = "DatingApp";
            $username = "root";
            $pw = "1234";
            $dbh = new PDO("mysql:host=$hostname;dbname=$dbname", "$username", "$pw");
        } catch (PDOException $e) {
            // Error al conectar con la base de datos
            echo "Failed to get DB handle: " . $e->getMessage() . "\n";
            exit;
        }

        try {
            // Inicia la inserción del like en la base de datos
            echo "Comença la inserció<br>";
            // Preparar la sentencia SQL para insertar el like
            $stmt = $dbh->prepare("INSERT INTO UserLikes (UserId, LikedUserId) VALUES(?,?)");
            // Ejecutar la sentencia pasando los parámetros
            $stmt->execute(array($_SESSION['user_data']['IdUser'], $likedUserID));
            echo "Insertat!";  // Mensaje de éxito
        } catch (PDOExecption $e) {
            // En caso de error en la inserción
            print "Error!: " . $e->getMessage() . " Desfem</br>";
        }
    }
}

// Función que verifica si se ha producido un "match"
function isAMatch()
{
    // Comprobamos si se ha recibido el id del usuario al que se le dio el "like"
    if (isset($_POST["likedUserId"])) {

        $likedUserID = $_POST["likedUserId"];

        try {
            // Conexión a la base de datos
            $hostname = "localhost";
            $dbname = "DatingApp";
            $username = "root";
            $pw = "1234";
            $pdo = new PDO("mysql:host=$hostname;dbname=$dbname", "$username", "$pw");
        } catch (PDOException $e) {
            // Error al conectar con la base de datos
            echo "Failed to get DB handle: " . $e->getMessage() . "\n";
            exit;
        }

        // Consultar si el usuario que le dio "like" ya le dio "like" a nuestro usuario
        $query = $pdo->prepare("SELECT 1 FROM UserLikes where LikedUserId = " . $_SESSION['user_data']["IdUser"] . " AND UserId = " . $likedUserID . ";");
        $query->execute();
        $isaMatch = $query->fetchColumn();

        if ($isaMatch !== false) {
            // Si es un match, se guarda en la base de datos
            saveANewMatch($likedUserID);
            $isaMatch = (int) $isaMatch;  // Convertir el valor a entero
        } else {
            $isaMatch = 0;  // Si no es un match, devolver 0
        }

        // Devolver el resultado como JSON
        echo json_encode($isaMatch);
        exit;
    }
}

// Función para guardar el match en la base de datos
function saveANewMatch($userLiked)
{
    try {
        // Conexión a la base de datos
        $hostname = "localhost";
        $dbname = "DatingApp";
        $username = "root";
        $pw = "1234";
        $dbh = new PDO("mysql:host=$hostname;dbname=$dbname", "$username", "$pw");
    } catch (PDOException $e) {
        // Error al conectar con la base de datos
        echo "Failed to get DB handle: " . $e->getMessage() . "\n";
        exit;
    }

    try {
        // Preparar la sentencia SQL para insertar el match en la base de datos
        $stmt = $dbh->prepare("INSERT INTO Matches (User1Id, User2Id) VALUES(?,?)");
        // Ejecutar la sentencia pasando los parámetros
        $stmt->execute(array($_SESSION['user_data']['IdUser'], $userLiked));

    } catch (PDOExecption $e) {
        // En caso de error en la inserción del match
        print "Error!: " . $e->getMessage() . " Desfem</br>";
    }
}



?>