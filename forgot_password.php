<!DOCTYPE html>
<html lang="en">

<?php
//Llegan los datos del usuario desde el LOGIN
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}else{
    session_destroy();
    session_start();
}

include_once 'apis.php'; 
include 'config.php';
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <title>Recuperar contraseña</title>
    <script src="password.js"></script>
    <link rel="stylesheet" href="styles.css">
    <script src="notifications.js"></script>
  
</head>

<body>
    <div class="containerMessage">
        <!-- Logo alineado a la izquierda -->
        <div class="header">
            <h1>IETINDER</h1>
        </div>
        <div class="fieldsContainerPass">
        <!-- Carga el formulario dependiendo del token -->
        <?php 
        if (isset($_GET["token"])) {
            echo "<div class='pass-div'><h1>Recuperar Contraseña</h1>
                <h3>Introduce tu nueva contraseña</h3>
                    <div class='field-p'>
                        <h3>Contraseña:</h3>
                        <input type='password' id='password' placeholder='******' required>
                    </div>
                    <div class='field-p'>
                        <h3>Repetir Contraseña:</h3>
                        <input type='password' id='password2' placeholder='******' required>
                    </div>
                       <div id='error-messagePass'>Las contraseñas no cumplen los requisitos o no coinciden.</div>
                    <button id='sendPass'>Actualizar Contraseña</button>

                  </div>";
        } else {
            echo "<div class='email-div'>
            <h1>Recuperar Contraseña</h1>
            <h3>Introduce tu email</h3>
                    <div class='field'>
                        <h3>Email:</h3>
                        <input type='email' id='email' placeholder='xxxx@ieti.site' required>
                    </div>
                    <button id='sendMail'>Enviar Enlace</button>
                  </div>";
        }
        
        ?>
        </div>
        <!-- Menú de navegación -->
        <nav class="bottom-nav">
            <h3><a href="#">Descubrir</a></h3>
            <h3><a href="messages.php#">Mensajes</a></h3>
            <h3><a href="profile.php">Perfil</a></h3>
        </nav>
    </div>
</body>

</html>



<?php 

if(isset($_GET["token"])){
    list($idUser, $mail) = explode(';', trim($_GET['token']));
   
    $idUser = decrypt($idUser, "grupo2Ietinder");
    $mail = decrypt($mail, "grupo2Ietinder");
   
    $isACorrectToken = isAVlidToken($idUser,$mail);

    if($isACorrectToken == 1){

        $_SESSION["user"]["id"] = $idUser;

    }else{
       
      
       
    }
    
}

if(isset($_GET["error"])){

    echo "<script>
            document.addEventListener('DOMContentLoaded', (event) => {
       
            showNotification('El token no es valido, intenta recuperar la contraseña de nuevo', 'error');
            
        }
        )

     </script>";
}

?>