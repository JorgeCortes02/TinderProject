<header>

    <?php
        $rutaActual = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
        $queryString = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
        
        if ($rutaActual === 'index.php') {
            echo '<a class="btn" onclick="deleteSession()">Salir</a>';
        }
        else if ($rutaActual === 'users.php' || $rutaActual === 'logs.php') {
            
            if (isset($queryString) && strpos($queryString, 'page=') !== false) {
                echo '<a href="index.php"><img src="/images/flecha-izquierda.png" alt="flecha para volver al panel de administrador"></a>';
            } else {
               
                $paginaAnterior = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';
                echo "<a href='{$paginaAnterior}'><img src='/images/flecha-izquierda.png' alt='volver'></a>";
            }
        }
        
    ?>
    
    <h1>IETINDER</h1>
</header>

