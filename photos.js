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

        // si es el primer panel, añadir el texto "Foto principal"
        // if (i === 0) {
        //     const mainPhotoText = $("<span>").text("Foto principal").addClass("main-photo-text");
        //     newPhotoPanel.append(mainPhotoText);
        // }

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

        // insertar la foto en el panelito de fotos -> empty() resetea, por así decirlo, el panel y añade la nueva imágen
        correspondingPhotoSpace.empty().append(photo);
        
        // si hay más de una foto crear la imagen de crucecita e insertarla en el div del panelito y la foto
        if (responseData.length > 1 || index > 0) {
            
            const closeImg = $("<img>").attr("src", "images/close.png").attr("alt", "Borrar foto " + (index + 1)).addClass("close-btn");
            correspondingPhotoSpace.append(closeImg);

            // añadir el evento de clic a la crucecita
            closeImg.on("click", () => {
                removePhoto(index, photoSpaces,userID);
            });
        }
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
            addNewPhoto(parentPanel,photoSpaces,userID);
        });
    }
}


// Paso 5: función para eliminar la foto y reorganizar las fotos
async function removePhoto(indexToRemove, photoSpaces,userID) {
    
    console.log("A la función remove le llegan los parámetros: indexToRemove -> ",indexToRemove," photoSpaces -> ",photoSpaces, " userID -> ",userID);

    // obtener todos los panelitos que contengan fotos
    const photoContainer = $('#containerPhotos');
    const photoPanels = photoContainer.find('.newPhotoPanel.noAdd');
    console.log("La medida de los container que NO puedes añadir es decir de 'photoPanels' es: ",photoPanels.length,"este es el photoPanels",photoPanels);

    // eliminar la foto del panel seleccionado
    const panelToClear = photoPanels.eq(indexToRemove);
    console.log("El panel de donde voy a eliminar la foto es ",panelToClear[0]);

    // obtener el identificador del panel a través de 'data-idPanel'
    const idPanel = panelToClear.attr('data-idPanel');
    console.log("El id del panel que voy a borrar es: "+idPanel);

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
            console.log(`Foto en el panel con ID ${idPanel} eliminada correctamente de la base de datos.`);

            // vaciar el contenido del panel
            panelToClear.empty();
            console.log("panel limpio: ",panelToClear[0]);

            // marcarlo como vacío para que se puedan añadir fotos
            console.log("Antes de cambiar clases:", panelToClear[0].className);
            panelToClear.removeClass('noAdd').addClass('yesAdd');
            console.log("Después de cambiar clases:", panelToClear[0].className);

            console.log("panelToClear es jQuery:", panelToClear instanceof jQuery);
            console.log("panel cambiado la clase: ",panelToClear[0]);

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

            // verificar si solo queda una foto y quitar la crucecita
            const remainingPanels = photoSpaces.filter(panel => panel.hasClass('noAdd'));
            if (remainingPanels.length === 1) {
                const onlyPhotoPanel = remainingPanels[0];
                onlyPhotoPanel.find('.close-btn').remove();
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
                
                    addNewPhoto(parentPanel,photoSpaces,userID);
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


// Paso 8: función de añadir fotos
function addNewPhoto(parentPanel,photoSpaces,userID) {

    console.log("--------------------\nEste es el photoSpaces antes de añadir la foto: ",photoSpaces);

    // Crear el input para seleccionar una foto
    const fileInput = $("<input>")
        .attr({
            type: "file",
            id: "fileInput",
            accept: ".jpg, .jpeg, .png, .webp",
        })
        .css({
            display: "none",
        });

    // Adjuntar el input al cuerpo para que funcione
    $("body").append(fileInput);

    // Simular clic en el input al llamar a esta función
    fileInput.click();

    fileInput.on("change", async function (e) {
        const file = e.target.files[0]; // Obtener el archivo seleccionado

        if (!file) {
            console.log("No se seleccionó ningún archivo.");
            fileInput.remove();
            return;
        }

        // Validar extensión del archivo
        const validExtensions = ["jpg", "jpeg", "png", "webp"];
        const fileExtension = file.name.split(".").pop().toLowerCase();

        if (!validExtensions.includes(fileExtension)) {
            alert("Formato de archivo no permitido. Solo se permiten JPG, JPEG, PNG y WEBP.");
            fileInput.remove();
            return;
        }

        // Crear un FormData para enviar el archivo al servidor
        const formData = new FormData();
        formData.append("userID", userID);
        formData.append("file", file);

        try {
            // Enviar la solicitud al servidor
            const response = await fetch("apis.php?api=uploadPhoto", {
                method: "POST",
                body: formData,
            });

            const result = await response.json();

            if (result.success) {
                displayPhotoInPanel(result.fileURL, parentPanel,photoSpaces,userID)
            }

            else{
                alert("Error al subir la foto: " + result.message);
            }

        }
        catch (error){
            console.error("Error al subir la foto:", error);
            alert("Ocurrió un error al intentar subir la foto.");
        }
        
        

        // Eliminar el input temporal
        fileInput.remove();
    });
}


// Función para mostrar la foto en el panel y gestionar el botón de añadir
function displayPhotoInPanel(photoURL, parentPanel,photoSpaces,userID) {

    // Añadir la nueva foto al panel actual
    const photo = $("<img>")
        .attr("src", photoURL) // URL de la foto subida
        .attr("alt", "Nueva foto")
        .css({ maxWidth: "100%", maxHeight: "100%", objectFit: "cover" });

    // Limpiar el panel actual, agregar la foto y cambiar su clase
    parentPanel.empty().append(photo).removeClass("yesAdd").addClass("noAdd");

    // Añadir el botón de cerrar para eliminar la foto
    const closeImg = $("<img>")
        .attr("src", "images/close.png")
        .attr("alt", "Cerrar foto")
        .addClass("close-btn");

    const indexToRemove = parentPanel.attr("data-idPanel");

    // darle su evento
    closeImg.on("click", function () {
        console.log("ELIMINANDO UNA FOTO RECÍEN AÑADIDA ----------------------------");
        console.log("EL INDICE DEL PANEL QUE LE VOY A PASAR ES: ",indexToRemove)
        removePhoto(indexToRemove, photoSpaces, userID);
    });
    
    parentPanel.append(closeImg);

    // Buscar el siguiente panel vacío
    const photoContainer = $('#containerPhotos');
    const photoPanels = photoContainer.find('.newPhotoPanel');

    // Buscar el siguiente panel vacío (con clase yesAdd)
    const nextEmptyPanel = photoPanels.filter('.yesAdd').first();

    // si ha encontrado otro panel, ya que puede que no haya más -> añade la imagen para añadir
    if (nextEmptyPanel.length > 0) {
        // Crear la imagen de "add.png"
        const addImg = $("<img>").attr("src", "images/add.png").attr("alt", "Añadir foto").css({ cursor: "pointer" });

        // Limpiar el siguiente panel vacío, añadir la imagen de "añadir" y vincular el evento
        nextEmptyPanel.empty().append(addImg);

        // Añadir el evento de clic para añadir una nueva foto
        addImg.on("click", function () {
            const parentPanel = $(this).closest('.newPhotoPanel');
            console.log("Añadir foto en el panel con ID:", parentPanel.attr('data-idPanel'));
            addNewPhoto(parentPanel,photoSpaces, userID);
        });
    }

    // verificar si es la segunda foto añadida así ponemos la crucecita a la primera
    if(indexToRemove == 1){
        console.log("Es la segunda foto añadida, se pone la crucecita a la primera");

        const previousPanel = photoSpaces.find(panel => panel.attr('data-idPanel') == (indexToRemove - 1));
        console.log("Este es el panel previo ",previousPanel[0]);

        // comprobar que existe y que tiene foto, lo cual tiene que tener siempre, pero porsiac
        if (previousPanel && previousPanel.hasClass('noAdd')) {
            console.log("Se va a poner la crucecita")

            // crear la crucecita
            const closeImg = $("<img>")
            .attr("src", "images/close.png")
            .attr("alt", "Cerrar foto")
            .addClass("close-btn");

            // añadir el evento
            closeImg.on("click", function () {
                const previousIndex = previousPanel.attr('data-idPanel');
                console.log("Eliminando foto del panel anterior con ID:", previousIndex);
                removePhoto(previousIndex, photoSpaces, userID);
            });

            // Añadir la crucecita al panel anterior
            previousPanel.append(closeImg);

        } else {
            console.log("El panel anterior ya tiene una crucecita.");
        }
        
    }


}