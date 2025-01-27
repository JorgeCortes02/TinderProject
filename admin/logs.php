<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="styles.css?t=<?php echo time();?>" type="text/css">

    <title>Logs listados</title>
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

// mostrar los datos de un log
if (isset($_GET['log'])) {
    $log = $_GET['log'];

    echo "<body id='logData'>";
    include('header.php');
    echo "<main>";

    $archivo = basename($log);
    $rutaArchivo = $_SERVER['DOCUMENT_ROOT']. '/logs/' . $archivo;

    if (file_exists($rutaArchivo)) {
        echo "<h1>Contenido del log: {$archivo}</h1>";
        echo "<pre>" . htmlspecialchars(file_get_contents($rutaArchivo)) . "</pre>";
    }
    else {
        echo "<p>El archivo no existe.</p>";
    }

    echo "</main>    
    </body>";
}

// mostrar los logs
else{

    echo "<body id='logsAdmin'>";

    include('header.php');

    // Paginación
    $perPage = 25;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $start = ($page - 1) * $perPage;

    $carpeta = '../logs';
    // archivos de la carpeta
    $archivos = scandir($carpeta);
    // Filtrar los resultados para excluir "." y ".."
    $archivos = array_diff($archivos, array('.', '..'));
    // total archivos
    $totalArchivos = count($archivos);


    if ($totalArchivos > 0) {
        echo "<main>";

        $totalPaginas = ceil($totalArchivos / $perPage);

        // obtener solo los archivos para la página actual
        $archivosPaginados = array_slice($archivos, $start, $perPage);

        // Tabla de archivos
        echo "<table border='1'>";
        echo "<tr class='headerTable'><th>#</th><th>Fecha del log</th></tr>";

        // Contador para numerar los archivos
        $contador = $start + 1;

        // Recorrer e imprimir solo los archivos de la página actual
        foreach ($archivosPaginados as $archivo) {
            $archivoUrl = htmlspecialchars($archivo, ENT_QUOTES, 'UTF-8');

            echo "<tr>";
            echo "<td><a href='?log={$archivoUrl}'>{$contador}</a></td>";
            echo "<td><a href='?log={$archivoUrl}'>{$archivo}</a></td>";
            echo "</tr>";
            $contador++;
        }

        echo "</table>
        </main>";

        // Paginador
        echo "<div class='paginator'>";
        for ($i = 1; $i <= $totalPaginas; $i++) {
            // Si estamos en la página actual, resaltamos el enlace
            if ($i == $page) {
                echo "<strong>$i</strong> ";
            } else {
                echo "<a href='?page=$i'>$i</a> ";
            }
        }
        echo "</div>";
    } else {
        echo "<p>No hay archivos disponibles en esta carpeta.</p>";
    }


    echo "</body>";

}

?>

</html>