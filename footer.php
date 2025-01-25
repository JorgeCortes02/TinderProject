<?php
    $current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="bottom-nav">
    <h3 class ="footer-discover <?php echo ($current_page == 'discover.php') ? 'active' : '' ?>"><a href="discover.php">Descubrir</a></h3>
    <h3 class ="footer-message <?php echo ($current_page == 'messages.php') ? 'active' : '' ?>"> <a href="messages.php">Mensajes</a></h3>
    <h3 class ="footer-profile <?php echo ($current_page == 'profile.php') ? 'active' : '' ?>"><a href="profile.php">Perfil</a></h3>
</nav>