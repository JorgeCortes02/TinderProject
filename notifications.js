function showNotification(message, type) {
    let notificationContainer = document.getElementById('notification-container');
  
    //si no existe el contenedor, lo creamos
    if (!notificationContainer) {
        notificationContainer = document.createElement('div');
        notificationContainer.id = 'notification-container';
        notificationContainer.style.position = 'fixed';
        notificationContainer.style.top = '0';
        notificationContainer.style.left = '50%';
        notificationContainer.style.transform = 'translateX(-50%)';
        notificationContainer.style.display = 'flex';
        notificationContainer.style.flexDirection = 'column';
        notificationContainer.style.gap = '10px';
        notificationContainer.style.zIndex = '9999';
        document.body.appendChild(notificationContainer);
    }
    //crear la notificación
    const notification = document.createElement('div');
    notification.classList.add('notification');
    //estilos notificación
    notification.style.backgroundColor = '#8B8EF9';
    notification.style.width = '460px';
    notification.style.height = '100px';
    notification.style.padding = '10px';
    notification.style.borderRadius = '5px';
    notification.style.color = '#fff';
    notification.style.fontFamily = 'Roboto, sans-serif';
    notification.style.fontWeight = 'bold';
    notification.style.fontSize = '18px';
    notification.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)';
    notification.style.opacity = '0';
    notification.style.transition = 'opacity 0.5s ease-in-out, transform 0.5s ease-in-out';
    notification.style.position = 'relative';
    notification.style.transform = 'translateY(-100%)';//empieza fuera de la pantalla
    notification.style.display = 'flex';
    notification.style.alignItems = 'center';
    notification.style.justifyContent = 'center';
    //centrar icono y mensaje
    const notificationContent = document.createElement('div');
    notificationContent.style.display = 'flex';
    notificationContent.style.alignItems = 'center';
    notificationContent.style.justifyContent = 'center';
    notificationContent.style.textalign = 'center';
    notificationContent.style.width = '100%';
    notification.appendChild(notificationContent);
    //añadir icono a la izquierda
    const iconContainer = document.createElement('div');
    iconContainer.style.marginRight = '15px';
    iconContainer.style.fontSize = '30px';
    iconContainer.style.flexShrink = '0';//evitar que icono se reduzca en tamaño
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
 
 
 
 