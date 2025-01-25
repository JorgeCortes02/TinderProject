<?php
// Incluir el autoload de Composer
require 'vendor/autoload.php'; 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true); // Crear una instancia de PHPMailer

try {
    // Configuración del servidor SMTP
    $mail->isSMTP();  
    $mail->Host = 'smtp.gmail.com';  // Cambia esto si usas otro proveedor
    $mail->SMTPAuth = true;
    $mail->Username = 'jcortesblanquez.cf@iesesteveterradas.cat'; // Tu correo
    $mail->Password = 'wrhgoklxamuzrkrt';     // Tu contraseña (o contraseña de aplicación)
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Datos del correo
    $mail->setFrom('jcortesblanquez.cf@iesesteveterradas.cat', 'Jorge');
    $mail->addAddress('jorge.cortes.blanquez@gmail.com', 'Jorge'); // Cambia a tu destinatario
    $mail->Subject = 'Prueba de PHPMailer';
    $mail->Body    = 'Este es un mensaje de prueba enviado desde PHPMailer.';

    // Enviar el correo
    $mail->send();
    echo 'Correo enviado correctamente.';
} catch (Exception $e) {
    echo "Error al enviar el correo: {$mail->ErrorInfo}";
}

?>