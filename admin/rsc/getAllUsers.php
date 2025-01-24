<?php

include $_SERVER['DOCUMENT_ROOT'] . '/config.php';

function getAllUsers($page = 1) {
    // Conexión a la base de datos
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

    // Calcular el límite y el offset para la paginación
    $limit = 25;
    $offset = ($page - 1) * $limit;

    try {
        // Consulta para obtener los usuarios
        $query = $pdo->prepare("SELECT IdUser,UserName,LastName1,Email,CreateAt FROM User ORDER BY IdUser ASC LIMIT :limit OFFSET :offset");
        $query->bindParam(':limit', $limit, PDO::PARAM_INT);
        $query->bindParam(':offset', $offset, PDO::PARAM_INT);
        $query->execute();

        $users = $query->fetchAll(PDO::FETCH_ASSOC);

        // Contar el total de usuarios para calcular el número de páginas
        $totalQuery = $pdo->query("SELECT COUNT(*) as total FROM User");
        $totalUsers = $totalQuery->fetch(PDO::FETCH_ASSOC)['total'];
        $totalPages = ceil($totalUsers / $limit);

        // Devolver los resultados en formato JSON
        echo json_encode([
            'success' => true,
            'users' => $users,
            'totalPages' => $totalPages,
            'currentPage' => $page
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error al ejecutar la consulta: ' . $e->getMessage()
        ]);
    }
}

// Recoger el número de página desde la petición
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
getAllUsers($page);