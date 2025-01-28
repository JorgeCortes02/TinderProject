// Aquí puedes usar jQuery sin problemas
$(document).ready(function() {
    let indexToDownload = 0;  // Índice inicial para cargar más perfiles
    const dislikeButton = $(".dislike");  // Botón de dislike
    const likeButton = $(".like");  // Botón de like
    const preferencesButton = $("#distance");
    const closeButton = $("#close");
    const preferencesMenu = $(".divPreferences");
    $('#minAge, #maxAge').on('input', updateRangeAge);
    $('#maxDis').on('input', updateRangeDistance);
    updateRangeAge();
    updateRangeDistance();
    dislikeButton.click(()=>{

        let cards = Array.from($(".card"));  // Convertimos las tarjetas a un array

        // Si solo queda una tarjeta
        if(cards.length == 1){
            // Llamamos a la función para recuperar más perfiles de forma asíncrona
            (async () => {
                await downloadData(indexToDownload);
                indexToDownload = calcMaxId();  // Actualizamos el índice con el id máximo
            })();
        }   
        // Eliminamos la última tarjeta mostrada
        const actualCard = $(".card-container").children(".card").last();
        sumPoints(200);
        actualCard.remove();
    });

    // Acción cuando el usuario hace clic en el botón de like
    likeButton.click(()=>{

        let cards = Array.from($(".card"));  // Convertimos las tarjetas a un array

        // Si solo queda una tarjeta
        if(cards.length == 1){
            // Llamamos a la función para recuperar más perfiles de forma asíncrona
            (async () => {
                await downloadData(indexToDownload);
                indexToDownload = calcMaxId();  // Actualizamos el índice con el id máximo
            })();           
        }
        // Seleccionamos la última tarjeta
        const actualCard = $(".card-container").children(".card").last();
        
        // Si no hay tarjetas para procesar, salimos de la función
        if (actualCard.length === 0) {
            console.log("No hay tarjetas para procesar");
            return;
        }
        
        // Guardamos el like y verificamos si es un match
        saveNewLike(actualCard.data("userId"));
        isMatch(actualCard.data("userId"));
        sumPoints(100);
        // Eliminamos la tarjeta que fue procesada
        actualCard.remove();
    });
    preferencesButton.on("click", function(){

        preferencesMenu.css("visibility", "visible");
    });

    closeButton.on("click", function(){
        
      
        const $maxDisValue = parseInt($('#maxDis').val());
        const $minAgeValue = parseInt($('#minAge').val());
        const $maxAgeValue = parseInt($('#maxAge').val());
        updateDisAndAgeMaxMin($maxAgeValue, $minAgeValue,$maxDisValue);
        console.log($maxAgeValue, $minAgeValue,$maxDisValue)
        $(".card-container").empty();
        (async () => {
            indexToDownload = 0;
            await downloadData(indexToDownload);
            indexToDownload = calcMaxId();  // Actualizamos el índice
            preferencesMenu.css("visibility", "hidden");

            showNotification("Filtros actualizados","info");
        })();       
    });
    // Cargamos los perfiles iniciales
    (async () => {
        await downloadData(indexToDownload);
        indexToDownload = calcMaxId();  // Actualizamos el índice
    })();
});

// Función para recuperar más datos (perfiles) y mostrarlos
async function downloadData(index) {
    const formData = new FormData();
    formData.append("indextToLoad", index);  // Añadir el índice a FormData

    try {
        // Hacemos la petición para obtener perfiles
        const response = await fetch("apis.php?api=downdloadProfiles", {
            method: "POST",  // Usamos el método POST
            body: formData  // Enviamos el FormData
        });

        const responseText = await response.text();  // Obtenemos la respuesta como texto
        console.log("Respuesta del servidor:", responseText); 

        // Si la respuesta no está vacía, intentamos parsear el JSON
        if (responseText && responseText.trim() != "") {
            const misDatos = JSON.parse(responseText);  // Intentamos parsear como JSON
            const contenedor = $(".card-container");  // Contenedor donde se muestran las tarjetas
            console.log(misDatos);
            
            // Si hay perfiles, los mostramos
            if (misDatos.length > 0) {
                misDatos.forEach(usuario => {
                   
                    if(usuario["TotalPoints"] != 0){

                        // contenedor genérico de la carta
                        const newCard = $("<div>").attr("class", "card");
                        newCard.attr("data-user-id", usuario["IdUser"]);  // Asignamos el id de usuario
                        
                        // creación del contenedor del carrusel -> img + puntitos
                        const carouselContainer = $("<div>").attr("class", "carousel-container card-img");
                        // creación del contenedor de las fotos
                        const photosContainer = $("<div>").attr("class", "photos-container");

                        // variables de control de imágenes para la creación de los puntitos
                        const images = [];
                        let imageIndex = 0;

                        // creación imágenes del carrusel
                        for (let i = 0; usuario[`img${i}`]; i++) {
                            const img = $("<img>")
                                .attr("src", usuario[`img${i}`])
                                .attr("class", "carousel-img")
                                .css("display", i === 0 ? "block" : "none");
                            
                            // añadido de la foto en el contenedor de fotos
                            photosContainer.append(img);
                            images.push(img);
                        }

                        // Crear puntitos carrusel
                        const dotsContainer = $("<div>").attr("class", "dots-container");
                        for (let i = 0; i < images.length; i++) {
                            const dot = $("<span>")
                                .attr("class", `dot ${i === 0 ? "active" : ""}`)
                                .on("click", () => {
                                    // Evento al hacer click en el puntito
                                    images[imageIndex].css("display", "none");
                                    $(".dot").eq(imageIndex).removeClass("active");
                                    imageIndex = i;
                                    images[imageIndex].css("display", "block");
                                    $(".dot").eq(imageIndex).addClass("active");
                                });
                            dotsContainer.append(dot);
                        }

                        // Añadir evento de clic en la imagen para pasar a la siguiente
                        carouselContainer.on("click", () => {
                            images[imageIndex].css("display", "none");

                            // Limitar la selección de dots al contenedor actual
                            const dots = carouselContainer.find(".dot");
                            dots.eq(imageIndex).removeClass("active");
                        
                            imageIndex = (imageIndex + 1) % images.length; // Siguiente imagen (vuelve al inicio si es la última)
                            
                            images[imageIndex].css("display", "block");
                            dots.eq(imageIndex).addClass("active");
                        });
                        
                        // añadir el contendor de fotos y de puntitos en el contenedor del carousel y añadir el carrousel al contenedor general
                        carouselContainer.append(photosContainer,dotsContainer);
                        newCard.append(carouselContainer);

                        // añadir la información al contenedor general
                        const inform = $("<div>").attr("class", "card-info");
                        inform.append($("<h2>").text(usuario["Username"] + ", " + usuario["UserAge"]));
                        newCard.append(inform);

                        contenedor.prepend(newCard);  // Insertamos la nueva tarjeta al principio
                            contenedor.css("background", "white");  // Cambiamos el estilo del contenedor

                    }

                });
            } else{
                // Si no hay más perfiles, mostramos un mensaje
                contenedor.prepend($("<h2>").text("No quedan perfiles por mostrar"));
              
            }
        }
    } catch (e) {
        console.error("Error al parsear JSON:", e);  // Si hay error, lo mostramos
    }
}

// Función para calcular el id máximo entre las tarjetas cargadas
function calcMaxId(){
    let maxId = 0;
    let actualCards = $(".card");  // Selecciona todos los elementos .card

    actualCards.each(function() {
        let userId = $(this).data("userId");  // Accede al valor de data-user-id
        if(userId > maxId){
            maxId = userId;  // Actualizamos el máximo id
        }
    });
    console.log(maxId);  // Mostramos el máximo id en consola
    return maxId;  // Retornamos el máximo id
}

// Función para guardar el like del usuario
function saveNewLike(idUser){
    const formData = new FormData();
    formData.append("likedUserId", idUser);  // Añadir el id del usuario al que se le dio like

    // Hacemos la petición para guardar el like
    fetch("apis.php?api=insertNewLike", {
        method: "POST",  // Usamos el método POST
        body: formData  // Enviamos el FormData
    })
        .then(response => {
            if (!response.ok) {  // Verificar si la respuesta fue exitosa
                throw new Error("Error al guardar los datos en el servidor");
            }
            console.log("Datos guardados exitosamente");  // Si la respuesta es correcta, mostramos un mensaje
        })
        .catch(error => {
            console.error("Error en la solicitud:", error);  // Si ocurre un error, lo mostramos
        });
}

// Función para verificar si es un match
function isMatch(idUser){
    const formData = new FormData();
    formData.append("likedUserId", idUser);  // Añadir el id del usuario al que se le dio like

    // Hacemos la petición para verificar el match
    fetch("apis.php?api=isAMatch", {
        method: "POST",  // Usamos el método POST
        body: formData,  // Enviamos el FormData
    })
    .then(response => response.json())  // Suponemos que la respuesta es JSON
    .then(data => {
        if(data == "1"){

            showPopup("¡Es un match!", "match")

        }// Mostramos el resultado del match en consola
    })
    .catch(error => {
        console.error("Error en la petición:", error);  // Si ocurre un error, lo mostramos
    });
}


function sumPoints(points){
    const formData = new FormData();
    formData.append("points", points);  // Añadir el id del usuario al que se le dio like
    console.log(points)
    // Hacemos la petición para verificar el match
    fetch("apis.php?api=sumPoints", {
        method: "POST",  // Usamos el método POST
        body: formData,  // Enviamos el FormData
    })
    .then(response => {
        if (!response.ok) {  // Verificar si la respuesta fue exitosa
            throw new Error("Error al guardar los datos en el servidor");
        }
        console.log("Datos guardados exitosamente");  // Si la respuesta es correcta, mostramos un mensaje
    })
    .catch(error => {
        console.error("Error en la solicitud:", error);  // Si ocurre un error, lo mostramos
    });

   
}

function updateRangeAge() {
    // Elementos del formulario
    const $minAge = $('#minAge');
    const $maxAge = $('#maxAge');
    const $minAgeValue = $('#minAgeValue');
    const $maxAgeValue = $('#maxAgeValue');
    const $progress = $('.range-slider .progress');

    // La edad mínima no puede ser superior a la edad máxima (se cambia el valor)
    if (parseInt($minAge.val()) > parseInt($maxAge.val())) {
        $minAge.val($maxAge.val());
    }

    // Se actualizan los valores de edad mínima y máxima en el formulario
    $minAgeValue.text($minAge.val());
    $maxAgeValue.text($maxAge.val());

    // Ajuste de la barra de progreso
    const minValue = parseInt($minAge.val());
    const maxValue = parseInt($maxAge.val());
    const range = 81; // Rango total (99 - 18)

    $progress.css({
        'left': ((minValue - 18) / range) * 100 + '%',
        'width': ((maxValue - minValue) / range) * 100 + '%'
    });

    
}
function updateRangeDistance() {
    // Elementos del formulario
    
    const $maxDis = $('#maxDis');
    
    //label
    const $maxDisValueLabel = $('#distanceLabel');
    const $progress = $('.range-slider .progress2');


    // Se actualizan los valores de edad mínima y máxima en el formulario
  
    $maxDisValueLabel.text($maxDis.val());


    const maxDisValue = parseInt($maxDis.val());
    const range = 200; // Rango total (99 - 18)

    $progress.css({
        'left': 0 * 100 + '%',
        'width': (maxDisValue  / range) * 100 + '%'
    });

   
}

function updateDisAndAgeMaxMin(){

    const formData = new FormData();
    formData.append("maxDistance", idUser);  // Añadir el id del usuario al que se le dio like


    // Hacemos la petición para guardar el like
    fetch("apis.php?api=insertNewLike", {
        method: "POST",  // Usamos el método POST
        body: formData  // Enviamos el FormData
    })
        .then(response => {
            if (!response.ok) {  // Verificar si la respuesta fue exitosa
                throw new Error("Error al guardar los datos en el servidor");
            }
            console.log("Datos guardados exitosamente");  // Si la respuesta es correcta, mostramos un mensaje
        })
        .catch(error => {
            console.error("Error en la solicitud:", error);  // Si ocurre un error, lo mostramos
        });


}

function updateDisAndAgeMaxMin(maxAge, minAge, maxDistance){

    const formData = new FormData();
    formData.append("maxAge", maxAge);  
    formData.append("minAge", minAge);  
     formData.append("maxDistance", maxDistance);  

    // Hacemos la petición para guardar el like
    fetch("apis.php?api=uploadNewDisMaxAndMinorAge", {
        method: "POST",  // Usamos el método POST
        body: formData  // Enviamos el FormData
    })
        .then(response => {
            if (!response.ok) {  // Verificar si la respuesta fue exitosa
                throw new Error("Error al guardar los datos en el servidor");
            }
            console.log("Datos guardados exitosamente");  // Si la respuesta es correcta, mostramos un mensaje
        })
        .catch(error => {
            console.error("Error en la solicitud:", error);  // Si ocurre un error, lo mostramos
        });


}