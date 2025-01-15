function showPopup(message, type) {
    let popupContainer = document.getElementById('popup-container');

    //si no existe el contenedor, lo creamos
    if (!popupContainer) {
        popupContainer = document.createElement('div');
        popupContainer.id = 'popup-container';
        document.body.appendChild(popupContainer);
    }
    //crear el popup
    const popup = document.createElement('div');
    popup.id = 'popup';

    //crear div popup-content
    const popupContent = document.createElement('div');
    popupContent.id = 'popup-content';
    popup.appendChild(popupContent);

    //añadir icono
    const iconContainer = document.createElement('div');
    iconContainer.id = 'popup-icon';
    popupContent.appendChild(iconContainer);

    //INTRODUCIR ICONO SEGUN TIPO
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

    //añadir mensaje+
    const messageContainer = document.createElement('div');
    messageContainer.id = 'popup-message';
    messageContainer.textContent = message;
    popupContent.appendChild(messageContainer);

    //añadir popup al contenedor
    popupContainer.appendChild(popup);


    //INTRODUCIR BOTONES SEGUN TIPO
    switch (type) {
        case 'error':
            break;
        case 'warning':
            break;
        case 'success':
            break;
        case 'info':
            break;
        case 'match':
            //añadir div para botones
            const buttonsContainer = document.createElement('div');
            buttonsContainer.id = 'buttons-Container';
            popupContent.appendChild(buttonsContainer);
        
            let buttonsContainerDiv = document.getElementById('buttons-Container');
        
            //añadir botones
            const buttonGoToChat = document.createElement('button');
            buttonGoToChat.id = 'button-goToChat';
            buttonGoToChat.textContent = "Ir al chat";
            //para cerrar el popup e ir al chat
            buttonGoToChat.addEventListener("click", () => {
                popup.style.opacity = '0';
                popup.style.transform = 'translateY(-100%)';
                    setTimeout(() => {
                        popup.remove();
                        // Redireccionamos a la página "messages.php"
                        window.location.href = "messages.php";
                    }, 500);
                });
            buttonsContainerDiv.appendChild(buttonGoToChat);
        
            const buttonContinueDiscovering = document.createElement('button');
            buttonContinueDiscovering.id = 'button-continueDiscovering';
            buttonContinueDiscovering.textContent = "Seguir descubriendo";
            //para cerrar el popup
            buttonContinueDiscovering.addEventListener("click", () => {
                popup.style.opacity = '0';
                popup.style.transform = 'translateY(-100%)';
                    setTimeout(() => {
                        popup.remove();
                    }, 500);
                });
            buttonsContainerDiv.appendChild(buttonContinueDiscovering);
                break;
    }


    //MOSTRAR POPUP (slide abajo)
    setTimeout(() => {
        popup.style.opacity = '1';
        popup.style.transform = 'translateY(10px)';
    }, 100);
}



