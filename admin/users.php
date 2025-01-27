<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="styles.css" type="text/css">

    <title>Usuarios listados</title>
</head>


<?php

session_start();

include_once '../apis.php'; 

// si no estás identificado -> error403
if (!isset($_SESSION['user_data'])) {
    logServer("Acceso no autorizado: usuario no identificado ha intentado entrar en el panel de administración");
    header("HTTP/1.1 403 Forbidden");
    include '../errors/error403.php';
    die();
}

// si estás identificado pero no tienes los permisos -> error401
if ($_SESSION['user_data']['Role'] !== 'Admin') {
    logServer("Acceso denegado: usuario con ID {$_SESSION['user_data']['ID']} intentó acceder sin permisos de administrador.");
    header("HTTP/1.1 401 Unauthorized");
    include '../errors/error401.php';
    die();
}

// conexión a la bd
include $_SERVER['DOCUMENT_ROOT'] . '/config.php';
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

// mostrar los datos de un usuario
if (isset($_GET['id'])) {
    $userId = $_GET['id'];

    echo "<body id='userData'>";
    include('header.php');
    echo "<main>
    <div id='data'>";

    // mostrar los datos
    $query = $pdo->prepare("SELECT * FROM User WHERE IdUser = :id");
    $query->bindParam(':id', $userId, PDO::PARAM_INT);
    $query->execute();
    $user = $query->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        
        foreach ($user as $key => $value) {
            // Usamos ucfirst() para dar formato a la primera letra de cada campo
            $label = ucfirst($key); 
            
            // Convertir la etiqueta de la clave para hacerlo más legible, si es necesario
            // Ejemplo: 'CreateAt' a 'Created At'
            $label = preg_replace('/([a-z])([A-Z])/', '$1 $2', $label); 
    
            echo "<p><strong>{$label}:</strong> {$value}</p>";
        }
    
        
    } else {
        // Si el usuario no existe, mostrar un mensaje
        echo "<p>Usuario no encontrado.</p>";
    }

    echo "</div>
        <aside>";

    // mostrar las fotos
    $query = $pdo->prepare("SELECT PhotoId, URL FROM Photo where UserId= :id");
    $query->bindParam(':id', $userId, PDO::PARAM_INT);
    $query->execute();
    $photos = $query->fetchAll(PDO::FETCH_ASSOC);

    if($photos){

        foreach( $photos as $photo){

            echo "<div class = 'photo'>
                    <p><strong>ID imagen:</strong> {$photo['PhotoId']}</p><p>URL: {$photo['URL']}</p>
                    <img src='{$photo['URL']}' alt='imagen con url {$photo['URL']}'>
                  </div>";
        }

    }
    else{
        echo "<p>No se han encontrado fotografías para este usuario</p>";
    }

    echo "</aside>
    </main>
    
    </body>";
}

// mostrar los usuarios
else{

    echo "<body id='usersAdmin'>";

    include('header.php');

    // Paginación
    $perPage = 25; // Número de usuarios por página
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $start = ($page - 1) * $perPage;

    // Consulta para obtener los usuarios
    $query = $pdo->prepare("SELECT IdUser,Username,LastName1,Email,CreateAt FROM User ORDER BY IdUser ASC LIMIT :limit OFFSET :offset");
    $query->bindParam(':limit', $perPage, PDO::PARAM_INT);
    $query->bindParam(':offset', $start, PDO::PARAM_INT);
    $query->execute();
    $users = $query->fetchAll(PDO::FETCH_ASSOC);

    $totalQuery = $pdo->query("SELECT COUNT(*) FROM User");
    $totalUsers = $totalQuery->fetchColumn();
    $totalPages = ceil($totalUsers / $perPage);

    echo "<main>";
    echo "<table border='1'>";
    echo "<tr class='headerTable'><th>Id</th><th>Username</th><th>LastName</th><th>Email</th><th>CreateAt</th></tr>";

    foreach ($users as $user) {
        echo "<tr>
                <td><a href='?id={$user['IdUser']}'>".$user['IdUser']."</a></td>
                <td><a href='?id={$user['IdUser']}'>".$user['Username']."</a></td>
                <td><a href='?id={$user['IdUser']}'>".$user['LastName1']."</a></td>
                <td><a href='?id={$user['IdUser']}'>".$user['Email']."</a></td>
                <td><a href='?id={$user['IdUser']}'>".$user['CreateAt']."</a></td>
              </tr>";
    }

    echo "</table>";
    echo "</main>";

    $maxVisiblePages = 5; // Número máximo de páginas a mostrar en el paginador
    $halfRange = floor($maxVisiblePages / 2); // Rango de páginas a mostrar hacia adelante y hacia atrás

    echo "<div class='paginator'>";

    // Mostrar el botón para ir a la primera página
    if ($page > 1) {
        echo "<a href='?page=1' class='paginate-btn'>&lt;&lt;</a>"; // Botón "Primera"
    }
    
    // Mostrar las páginas cercanas a la página actual
    $startPage = max(1, $page - $halfRange);
    $endPage = min($totalPages, $page + $halfRange);
    
    // Asegurar que siempre haya al menos una página visible
    if ($startPage > 1) {
        echo "<a href='?page=1' class='paginate-btn'>1</a>";
        if ($startPage > 2) {
            echo "<span class='paginate-btn'>...</span>"; // Elipsis si hay más páginas antes
        }
    }
    
    for ($i = $startPage; $i <= $endPage; $i++) {
        $activeClass = ($i == $page) ? 'active' : ''; // Resaltar la página activa
        echo "<a href='?page=$i' class='paginate-btn $activeClass'>$i</a> ";
    }
    
    if ($endPage < $totalPages) {
        if ($endPage < $totalPages - 1) {
            echo "<span class='paginate-btn'>...</span>"; // Elipsis si hay más páginas después
        }
        echo "<a href='?page=$totalPages' class='paginate-btn'>$totalPages</a>"; // Última página
    }
    
    // Mostrar el botón para ir a la última página
    if ($page < $totalPages) {
        echo "<a href='?page=$totalPages' class='paginate-btn'>&gt;&gt;</a>"; // Botón "Última"
    }

    echo "</div>
    
    </body>";

}

?>
</html>