<?php

include $_SERVER['DOCUMENT_ROOT'] . '/config.php';

function getUserData(){

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {

        $userId = $_GET['userId'];

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

        try {
            // Consulta para obtener los usuarios
            $query = $pdo->prepare("SELECT * FROM User WHERE IdUser = :id");
            $query->bindParam(':id', $userId, PDO::PARAM_INT);
            $query->execute();
            $dataUser = $query->fetchAll(PDO::FETCH_ASSOC);
    
            // Devolver los resultados en formato JSON
            echo json_encode([
                'success' => true,
                'dataUser' => $dataUser
            ]);
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al ejecutar la consulta: ' . $e->getMessage()
            ]);
        }

    }
}