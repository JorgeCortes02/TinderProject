function showNotification(message, type) {
    // Buscar o crear un contenedor de notificaciones global en el <body>
    let notificationContainer = $('#notification-container');

    if (notificationContainer.length === 0) {
        console.warn('No se encontró el contenedor de notificaciones. Creándolo automáticamente.');
        notificationContainer = $('<div id="notification-container"></div>').appendTo('body');
    }

    // Crear la notificación
    const notification = $('<div class="notification"></div>');
    const notificationContent = $('<div class="notificationContent"></div>').appendTo(notification);
    const iconContainer = $('<div class="iconContainer"></div>').appendTo(notificationContent);

    // Cambiar icono según el tipo
    switch (type) {
        case 'error':
            iconContainer.html('<i class="fas fa-times"></i>'); // X
            break;
        case 'warning':
            iconContainer.html('<i class="fas fa-exclamation-triangle"></i>'); // Triángulo !
            break;
        case 'success':
            iconContainer.html('<i class="fas fa-check"></i>'); // Tick
            break;
        case 'info':
            iconContainer.html('<i class="fas fa-info-circle"></i>'); // Info
            break;
        case 'match':
            iconContainer.html('<i class="fas fa-heart"></i>'); // Corazón
            break;
        default:
            console.warn('Tipo de notificación desconocido:', type);
            iconContainer.html('<i class="fas fa-question-circle"></i>'); // Icono por defecto
            break;
    }

    // Añadir mensaje a la derecha
    $('<div class="messageContainer"></div>')
        .text(message)
        .appendTo(notificationContent);

    notificationContainer.append(notification);

    setTimeout(() => {
        notification.css({
            opacity: '1',
            transform: 'translateY(0)',
        });
    }, 100);

    setTimeout(() => {
        notification.css({
            opacity: '0',
            transform: 'translateY(-20px)',
        });

        setTimeout(() => {
            notification.remove();
        }, 500);
    }, 5000);
}

// Exponer la función en el objeto global
window.showNotification = showNotification;