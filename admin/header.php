<header>

    <?php
        $rutaActual = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
        $queryString = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
        if ($rutaActual === 'index.php') {
            echo '<a class="btn" onclick="deleteSession()">Salir</a>';
        }
        else{
            
            if (isset($queryString) && !str_contains($queryString, 'page=')) {
               if($rutaActual === "logs.php"){
                echo "<a href='logs.php'><img src='/images/flecha-izquierda.png' alt='volver'></a>";
                }else{  
                    echo "<a href='users.php'><img src='/images/flecha-izquierda.png' alt='volver'></a>";
                }
            } else {
               
                echo '<a href="index.php"><img src="/images/flecha-izquierda.png" alt="flecha para volver al panel de administrador"></a>';
            }
        }
    ?>
    
    <h1>IETINDER</h1>
</header>

