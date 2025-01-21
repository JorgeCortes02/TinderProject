$(document).ready(function() {

    // Paso 1: cargar las imágenes
    (async () => {

        // obtener el id del usuario
        const userID = await getUserID();

        if (userID) {
            await downloadImages(userID);
        } else {
            console.error("No se pudo obtener el userID.");
        }
    })();

});

async function getUserID() {
    try {
        const response = await fetch("apis.php?api=getUserID", {
            method: "POST"
        });

        // Obtener la respuesta como JSON
        const data = await response.json();

        // verificar si la respuesta contiene el userID
        if (data.userID) {
            console.log("UserID obtenido:", data.userID);
            return data.userID;
        } else {
            console.error("Error al obtener el userID:", data.error);
        }
    } catch (error) {
        console.error("Error en la solicitud:", error);
    }
}


// Paso 2: función de descargar las imágenes
async function downloadImages(userID)  {
    try {

        // crear un objeto FormData y agregar el userID
        const formData = new FormData();
        formData.append('userID', userID);

        // hacer la petición para obtener las fotografías
        const response = await fetch("apis.php?api=downloadImages", {
            method: "POST",
            body: formData
        });

        // obtener la respuesta como JSON
        const responseData = await response.json();
        console.log("Respuestas de fotos:", responseData);

        // si la respuesta es correcta crear los contenedores
        if (responseData && Array.isArray(responseData)) {
            createPhotoContainers(responseData,userID);
        }
    }    
    catch (e) {
        console.error("Error al parsear JSON:", e);
    }
}



// función de crear los panelitos
function createPhotoContainers(responseData,userID) {
    const photoContainer = $('#containerPhotos');

    // Paso 3: crear los seis paneles grises con la clase 'yesAdd'

    // crear un array para guardar los paneles creados
    const photoSpaces = []; 

    for (let i = 0; i < 6; i++) {
        const newPhotoPanel = $("<div>").attr("class", "newPhotoPanel yesAdd");
        newPhotoPanel.attr("data-idPanel", i);

        photoContainer.append(newPhotoPanel);
        photoSpaces.push(newPhotoPanel);
    }

    // Paso 4: agregar las fotos del usuario en los paneles
    responseData.forEach((url, index) => {

        // crear la foto
        const photo = $("<img>").attr("src", url).attr("alt", "Foto " + (index + 1));

        // cambiar la clase del panel que contiene la foto a 'noAdd' -> obtener el objeto a través del array de panelitos
        const correspondingPhotoSpace = photoSpaces[index];
        correspondingPhotoSpace.removeClass('yesAdd').addClass('noAdd');

        // insertar la foto en el panelito de fotos -> empty() reemplaza el contenido del panel con la nueva imágen
        correspondingPhotoSpace.empty().append(photo);
        
        // crear la imagen de crucecita e insertarla en el div del panelito y la foto
        const closeImg = $("<img>").attr("src", "images/close.png").attr("alt", "Borrar foto " + (index + 1)).addClass("close-btn");
        correspondingPhotoSpace.append(closeImg);

        // añadir el evento de clic a la crucecita
        closeImg.on("click", () => {
            removePhoto(index, photoSpaces,userID);
        });
    });

    // Buscar el primer panel vacío que tiene la clase 'yesAdd' y agregarle la imagen de añadir foto
    const firstEmptyPanel = photoSpaces.find(panel => panel.hasClass('yesAdd'));

    // Solo agregar la imagen de "añadir foto" en el primer panel vacío
    if (firstEmptyPanel) {
        const addImg = $("<img>").attr("src", "images/add.png").attr("alt", "Añadir foto").css({
            cursor: "pointer",
        });

        firstEmptyPanel.empty().append(addImg); // añadir la imagen de añadir en el primer panel vacío

        // Añadir el evento de clic para que cuando se haga clic, se pueda agregar una nueva foto
        addImg.on("click", function () {
            const parentPanel = $(this).closest('.newPhotoPanel');
            console.log("Añadir foto en el panel con ID:", parentPanel.attr('data-idPanel'));
            addNewPhoto(parentPanel);  // Lógica para añadir una nueva foto
        });
    }
}


// Paso 5: función para eliminar la foto y reorganizar las fotos
async function removePhoto(indexToRemove, photoSpaces,userID) {

    // obtener todos los panelitos que contengan fotos
    const photoContainer = $('#containerPhotos');
    const photoPanels = photoContainer.find('.newPhotoPanel.noAdd');

    // eliminar la foto del panel seleccionado
    const panelToClear = photoPanels.eq(indexToRemove);

    // obtener el identificador del panel a través de 'data-idPanel'
    const idPanel = panelToClear.attr('data-idPanel');
    // obtener la URL de la imagen dentro del panelito pulsado
    const imgSrc = panelToClear.find('img').attr('src');
    console.log("URL de la foto:", imgSrc);

    // crear un objeto FormData y agregar el userID y el idPanel
    const formData = new FormData();
    formData.append('userID', userID);
    formData.append('imgSrc', imgSrc);

    try {
        const response = await fetch('apis.php?api=deletePhoto', {
            method: 'POST',
            body: formData
        });

        // obtener la respuesta como JSON
        const data = await response.json();


        if (data.success) {
            console.log(`Foto con ID ${idPanel} eliminada correctamente de la base de datos.`);

            // vaciar el contenido del panel
            panelToClear.empty();

            // marcarlo como vacío para que se puedan añadir fotos
            panelToClear.removeClass('noAdd').addClass('yesAdd'); 

            // reorganizar las fotos que hay detrás del índice donde estaba la foto eliminada
            for (let i = indexToRemove; i < photoPanels.length - 1; i++) {
                const currentPanel = photoPanels.eq(i);
                const nextPanel = photoPanels.eq(i + 1);

                // Paso 6: Mover la foto del siguiente panel al actual

                // Obtener la foto menos la crucecita
                const nextPhoto = nextPanel.find('img').not('.close-btn'); 
                // Obtener la crucecita
                const nextCloseBtn = nextPanel.find('.close-btn'); 

                // mover contenido y poner ese div como 'noAdd'
                currentPanel.empty().append(nextPhoto).append(nextCloseBtn);
                currentPanel.removeClass('yesAdd').addClass('noAdd');

                // actualizar el evento de la crucecita para el nuevo índice
                nextCloseBtn.off('click').on('click', () => {
                    removePhoto(i, photoSpaces,userID);
                });

                // vaciar el siguiente panel ya que ya lo hemos subido
                nextPanel.empty().removeClass('noAdd').addClass('yesAdd');
            }

            // Paso 7: agregar la imagen de "añadir foto" en el primer panel vacío

            // buscar todos los paneles vacíos y borrar todo
            const emptyPanels = photoSpaces.filter(panel => panel.hasClass('yesAdd'));
            emptyPanels.forEach(panel => {
                panel.empty(); 
            });

            // buscar el primer panel vacío que tiene la clase 'yesAdd' y agregarle la imagen de añadir foto
            const firstEmptyPanel = photoSpaces.find(panel => panel.hasClass('yesAdd'));
            if (firstEmptyPanel) {

                // crear la imagen predeterminada
                const addImg = $("<img>").attr("src", "images/add.png").attr("alt", "Añadir foto").css({cursor: "pointer",});

                // añadir la imagen de añadir en el primer panel vacío
                firstEmptyPanel.empty().append(addImg);

                //Paso 8: actualiza el evento click para que añada la imágen
                addImg.off().on("click", function (e) {
                    // obtener el contenedor padre -> div con clase 'newPhotoPanel'
                    const parentPanel = $(this).closest('.newPhotoPanel');
                    console.log("Añadir foto en el panel con ID:", parentPanel.attr('data-idPanel'));

                    const idPanel = parentPanel.attr('data-idPanel');
                    console.log("has picado al panel con ID:", idPanel);
                
                    addNewPhoto(parentPanel);
                });
            }
        }        
        else {
            console.error(`Error al eliminar la foto con ID ${idPanel}:`, data.message);
        }
    } 
    
    catch (error) {
        console.error('Error en la solicitud de eliminación:', error);
    }
}


// función de añadir fotos
function addNewPhoto(parentPanel){

}