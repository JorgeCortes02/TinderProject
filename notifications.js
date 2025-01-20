import { logToServer } from './lib.js';

function showNotification(message, type) {
    let notificationContainer = document.getElementById('notification-container');
    logToServer('Notificacion - Mensaje: '+message+ " tipo: "+type);
    //si no existe el contenedor, lo creamos
    if (!notificationContainer) {
        notificationContainer = document.createElement('div');
        notificationContainer.id = 'notification-container';
        document.body.appendChild(notificationContainer);
    }
    //crear la notificación
    const notification = document.createElement('div');
    notification.classList.add('notification');

    //centrar icono y mensaje
    const notificationContent = document.createElement('div');
    notificationContent.classList.add('notificationContent');
    notification.appendChild(notificationContent);

    //añadir icono a la izquierda
    const iconContainer = document.createElement('div');
    iconContainer.classList.add('iconContainer');
    notificationContent.appendChild(iconContainer);

    //CAMBIO ICONO SEGUN TIPO
    switch (type) {
        case 'error':
            iconContainer.innerHTML = '<i class="fas fa-times"></i>';//X
            break;
        case 'warning':
            iconContainer.innerHTML = '<i class="fas fa-exclamation-triangle"></i>';//triangulo!
            break;
        case 'success':
            iconContainer.innerHTML = '<i class="fas fa-check"></i>';//tick
            break;
        case 'info':
            iconContainer.innerHTML = '<i class="fas fa-info-circle"></i>';//info
            break;
        case 'match':
            iconContainer.innerHTML = '<i class="fas fa-heart"></i>';//corazon
            break;
    }
    //añadir mensaje a la derecha
    const messageContainer = document.createElement('div');
    messageContainer.classList.add('messageContainer');
    messageContainer.textContent = message;
    notificationContent.appendChild(messageContainer);

    //añadir notificación al contenedor
    notificationContainer.appendChild(notification);

    //MOSTRAR NOTIFICACION (slide abajo)
    setTimeout(() => {
        notification.style.opacity = '1';
        notification.style.transform = 'translateY(10px)';
    }, 100);
    //ELIMINAR notificación a los 3seg (slide arriba)
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateY(-100%)';
        setTimeout(() => {
            notification.remove();
        }, 500);
    }, 3000);
 }
 
 
 
 