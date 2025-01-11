<?php
session_start();

/*
--Download, calc and order by distance for discover.

Las siguientes funciones son las necesarias para la descarga de datos para el discover.
Tenemos la que se encarga de calcular las distancias entre los dos puntos y devolverlas.
La función general que se encarga de llamar a la función que nos descarga los datos de la BBDD siguiendo los criterios de busqueda.
Y por último tenemos la función que se encarga de descargar los datos de la BBDD.

*/

// Función para calcular la distancia entre dos ubicaciones (en km)
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

    // Tu ubicación (latitud y longitud)
    $myLat = (float) $_SESSION["user_data"]["Latitude"]; // Ejemplo de latitud (Nueva York)
    $myLong = (float) $_SESSION["user_data"]["Longitude"]; // Ejemplo de longitud (Nueva York)

    $users = downloadUsersForDiscover();
    $users = downloadFotos($users);

    // Calcular distancia de cada usuario respecto a tu ubicación
    foreach ($users as &$user) {

        $user["distance"] = calcularDistance((float) $user["Latitude"], (float) $user["Longitude"], $myLat, $myLong);

    }

    usort($users, function ($a, $b) {

        return $a["distance"] - $b["distance"];

    });


    // Devolver los resultados como JSON
    header('Content-Type: application/json');
    echo json_encode($users); // Devuelve el array de usuarios como JSON
    exit;


}

//Descargamos los perfiles a mostrar en discover siguiendo nuestros criterios.
function downloadUsersForDiscover(): array
{



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
    $likesForDiscard = downloadOurLikes();
    $likesForDiscard = implode(",", $likesForDiscard);

    // Suponiendo que $_SESSION['user_data']["IdUser"] contiene el valor del usuario
    $userId = $_SESSION['user_data']["IdUser"];
    $maxAge = $_SESSION['user_data']["MaxAge"];
    $minAge = $_SESSION['user_data']["MinAge"];
    // Preparar la consulta de manera segura usando un marcador de posición para :userId
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
                AND MinAge >= :MinAge;"
    );

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
            $hostname = "localhost";
            $dbname = "DatingApp";
            $username = "root";
            $pw = "1234";
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
            echo "pollo";
        }



    }

    return $userDiccionari;

}


//Esta función se encarga de descargar los likes que hemos dados para que no nos muestre esos perfiles.
function downloadOurLikes()
{
    echo "holaa";
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

    $query = $pdo->prepare("SELECT LikedUserId FROM UserLikes where UserId = " . $_SESSION['user_data']["IdUser"] . ";");
    $query->execute();
    $likes = $result = $query->fetchAll(PDO::FETCH_COLUMN);
    return $likes;

}

print_r(CalcAndOrderbyPosition());

?>