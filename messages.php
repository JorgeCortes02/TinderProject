<?php 
session_start();
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Missatges</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="containerMessage">
        <!-- Encabezado -->
        <div class="header">
            <h1>IETINDER</h1>
            
        </div>

        <!-- Sección de matches -->
        <div class="matches-section">
            <h3>Els meus matches</h3>
            <div class="matches-list">
                <?php 
                include_once 'apis.php'; 
                include 'config.php';
                $matchDiccionari = downloadFotosForMatches(downloadMatches());

                if (!empty($matchDiccionari)) { // Verifica si $matchDiccionari tiene datos
                    foreach ($matchDiccionari as $match) {
                        echo "
                            <div class='match-item' data-id='" . htmlspecialchars($match["MatchId"]) . "'>
                                <img src='" . htmlspecialchars($match["img"]) . "' alt='Match Image'>
                            </div>
                        ";
                    }
                } else {
                    echo "
                        <div class='no-matches'>
                            <h4>No hay matches disponibles en este momento.</h4>
                        </div>
                    ";
                }
                ?>
            </div>
</div>

        <!-- Sección de mensajes -->
        <div class="messages-section">
            <h3>Missatges</h3>
            <div class="message-list">
               <?php 
               
               $messageDiccionari = downloadFotosForChats(downloadChats());

               if (!empty($messageDiccionari)) { // Verifica si $messageDiccionari tiene datos
                   foreach ($messageDiccionari as $conver) {
                       echo "
                       <a href='conversa.html' class='message-item'>
                           <img src='" . htmlspecialchars($conver["img"]) . "' alt='Foto de Perfil'>
                           <div class='message-info'>
                               <p class='user-name'>" . htmlspecialchars($conver["username"]) . "</p>
                               <p class='last-message'>" . htmlspecialchars($conver["Text"]) . "</p>
                           </div>
                       </a>
                       ";
                   }
               } else {
                   echo "
                    <div class='no-matches'>
                            <h4>No hay mensajes disponibles en este momento.</h4>
                        </div>
                   ";
               }
               ?>
            
               
            </div>
        </div>

        <!-- Menú de navegación inferior -->
        <nav class="bottom-nav">
            <a href="discover.php">Descobrir</a>
            <a href="messages.php" class="active">Missatges</a>
            <a href="profile.php">Perfil</a>
        </nav>
    </div>
</body>
</html>


<?php 

function downloadMatches(): array
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



        $query = $pdo->prepare(
            "SELECT MatchId,
                    User1Id,
                    User2Id
                    FROM Matches m 
                    WHERE m.MatchId NOT IN (
                        SELECT DISTINCT MatchId
                        FROM Message 
                    ) AND (User1Id  = :userId OR User2Id  = :userId) ; "
        );
    

    // Ejecutar la consulta con los parámetros correspondientes
    $query->execute([
        ':userId' => $userId
    ]);
    $matchDiccionari = $query->fetchAll(PDO::FETCH_ASSOC);
    return $matchDiccionari;
}

function downloadFotosForMatches($matchDiccionari)
{

    foreach ($matchDiccionari as &$match) {

        try {
            global $username, $pw;
            $hostname = "localhost";
            $dbname = "DatingApp";
            $pdo = new PDO("mysql:host=$hostname;dbname=$dbname", "$username", "$pw");
        } catch (PDOException $e) {
            echo "Failed to get DB handle: " . $e->getMessage() . "\n";
            exit;
        }

        if($match["User1Id"] ==  $_SESSION['user_data']["IdUser"]){

            $query = $pdo->prepare("SELECT URL FROM Photo where UserId = " . $match["User2Id"] . " LIMIT 1;");

        }else{

            $query = $pdo->prepare("SELECT URL FROM Photo where UserId = " . $match["User1Id"] . " LIMIT 1;");
        }
    
        $query->execute();
        $photos = $query->fetchAll(PDO::FETCH_COLUMN);

       
        foreach ($photos as $img) {

            $match["img"] = $img;
        
        }

    }

    return $matchDiccionari;

}

function downloadChats(){


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


    $query = $pdo->prepare(
        "SELECT m.MatchId, m.ReceiverUserId, m.SenderUserId, m.Text, m.SentAt
FROM Message m
JOIN (
    SELECT MatchId, MAX(MessageId) AS LastMessageId
    FROM Message
    WHERE ReceiverUserId = :userId OR SenderUserId = :userId
    GROUP BY MatchId
) AS sub ON m.MessageId = sub.LastMessageId
ORDER BY m.SentAt DESC;"
    );
                    

    // Ejecutar la consulta con los parámetros correspondientes
    $query->execute([
        ':userId' => $userId
    ]);
    $messageDiccionari = $query->fetchAll(PDO::FETCH_ASSOC);
    return $messageDiccionari ;


}

function downloadFotosForChats($messageDiccionari)
{

    foreach ($messageDiccionari as &$conver) {

        try {
            global $username, $pw;
            $hostname = "localhost";
            $dbname = "DatingApp";
            $pdo = new PDO("mysql:host=$hostname;dbname=$dbname", "$username", "$pw");
        } catch (PDOException $e) {
            echo "Failed to get DB handle: " . $e->getMessage() . "\n";
            exit;
        }
        $userId = $_SESSION['user_data']["IdUser"];
        if($conver["ReceiverUserId"] ==  $_SESSION['user_data']["IdUser"]){

            $query = $pdo->prepare("SELECT u.Username, p.URL FROM User u 
                        LEFT JOIN Photo p ON u.IdUser = p.UserId 
                        WHERE u.IdUser = " . $conver["SenderUserId"] . " LIMIT 1;");

            

        }else{

            $query = $pdo->prepare("SELECT u.Username, p.URL FROM User u 
            LEFT JOIN Photo p ON u.IdUser = p.UserId 
            WHERE u.IdUser = " . $conver["ReceiverUserId"] . " LIMIT 1;");
        }
    
        $query->execute();
// Recuperar todos los resultados de la consulta
$photoData = $query->fetchAll(PDO::FETCH_ASSOC);

// Recorrer los resultados y asignar los valores al array $conver
foreach ($photoData as $data) {
    // Guardar Username y URL de la foto para cada usuario en $conver
    $conver["username"] = $data['Username'];
    $conver["img"] = $data['URL'];
}
    }
   
    return $messageDiccionari;

}
?>