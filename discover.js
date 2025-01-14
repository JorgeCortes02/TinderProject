// Aquí puedes usar jQuery sin problemas
$(document).ready(function() {
    let indexToDownload = 0;  // Índice inicial para cargar más perfiles
    const dislikeButton = $(".dislike");  // Botón de dislike
    const likeButton = $(".like");  // Botón de like
   
    

    // // Acción cuando el usuario hace clic en el botón de dislike
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
                    const newCard = $("<div>").attr("class", "card");
                    newCard.attr("data-user-id", usuario["IdUser"]);  // Asignamos el id de usuario
                    const inform = $("<div>").attr("class", "card-info");
                    inform.append($("<h2>").text(usuario["Username"] + ", " + usuario["UserAge"]));
                    newCard.append($("<img>").attr("src", usuario["img0"]).attr("class", "card-img"));  // Imagen del perfil
                    newCard.append(inform);
                    contenedor.prepend(newCard);  // Insertamos la nueva tarjeta al principio
                    contenedor.css("background", "white");  // Cambiamos el estilo del contenedor

                });
            } else{
                // Si no hay más perfiles, mostramos un mensaje
                contenedor.prepend($("<h2>").text("No quedan Perfiles por mostrar"));
                contenedor.css("background", "gray").css("opacity", 0.5);  // Cambiamos el estilo del contenedor
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
        console.log(data);  // Mostramos el resultado del match en consola
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