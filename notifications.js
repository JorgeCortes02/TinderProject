function showNotification(message, type) {
    // Buscar el primer hijo directo del <body>
    let parentContainer = $('body > *').first();

    // Si no se encuentra ningún hijo directo, crear un contenedor dentro del body
    if (parentContainer.length === 0) {
        console.warn('No se encontró ningún contenedor dentro del body. Creando uno automáticamente.');
        parentContainer = $('<div class="default-container"></div>').appendTo('body');
    }

    // Buscar o crear el contenedor de notificaciones dentro del contenedor encontrado
    let notificationContainer = parentContainer.find('#notification-container');

    if (notificationContainer.length === 0) {
        notificationContainer = $('<div id="notification-container"></div>').appendTo(parentContainer);
    }

    // Crear la notificación
    const notification = $('<div class="notification"></div>');

    // Contenido de la notificación (icono y mensaje)
    const notificationContent = $('<div class="notificationContent"></div>').appendTo(notification);

    // Añadir icono a la izquierda
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
    }

    // Añadir mensaje a la derecha
    const messageContainer = $('<div class="messageContainer"></div>').text(message).appendTo(notificationContent);

    // Añadir notificación al contenedor
    notificationContainer.append(notification);

    // Mostrar notificación (animación de slide hacia abajo)
    setTimeout(() => {
        notification.css({
            opacity: '1',
            transform: 'translateY(10px)'
        });
    }, 100);

    // Eliminar notificación después de 5 segundos (slide hacia arriba)
    setTimeout(() => {
        notification.css({
            opacity: '0',
            transform: 'translateY(-100%)'
        });

        setTimeout(() => {
            notification.remove();
        }, 500);
    }, 5000);
}