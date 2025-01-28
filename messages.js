$(document).ready(function() {

    let matches = Array.from($(".match-item"));

    let chats = Array.from($(".message-item"));

    let returnToMessagesButton = $("#returnToMessage").on("click",returnToInicialPage );
    
    const sentButton = $("#sent");

    sentButton.on("click", sentNewMissage);
    
    chats.forEach(element => {
        element.addEventListener("click", function(event){

            openChat(event, "chat");

        });
    });
    
    matches.forEach(element =>{
        element.addEventListener("click", function(event){

            openChat(event, "Match");

        });
    });
    
});


function openChat(event, type){
    
    const contenedorMatches =  $(".matches-section");
    const contenedorMessage =  $(".messages-section");
    const navBar = $(".bottom-nav").css("display", "none");
    contenedorMatches.css("display", "none");
    contenedorMessage.css("display", "none");
    const contenedorChat =  $(".containerChat").css("display", "flex");

    const profileButtom = $("#Profile");
    const chatButton = $("#Chat");
    chatButton.addClass("selectedTab");
    

    profileButtom.on("click", function(){

       profileButtom.prop("disabled", true);

       profileButtom.addClass('selectedTab');
       chatButton.removeClass('selectedTab');

        const contenedorMensages =  $(".chat");
        contenedorMensages.css("display", "none")
        const contenedorProfile = $(".card-profile");
        contenedorProfile.css("display", "flex")
       
        const contenedorSent =  $(".messageInputContainer");
        contenedorSent.css("display", "none")
        const contenedorChat =  $(".containerChat")
        downloadData(parseInt(contenedorChat.attr("data-otherlikeid")));
    })
    chatButton.on("click", function(){
        profileButtom.prop("disabled", false);

        chatButton.addClass('selectedTab');
        profileButtom.removeClass('selectedTab');

        const contenedorMensages =  $(".chat");
        contenedorMensages.css("display", "flex")
        const contenedorProfile = $(".card-profile");
        contenedorProfile.css("display", "none")
        const contenedorSent =  $(".messageInputContainer");
        contenedorSent.css("display", "flex")
        contenedorCardProfile = $(".card-profile")
        contenedorCardProfile.empty();
    })

    const id = event.currentTarget.dataset.id; // "123"
    const image = event.currentTarget.dataset.img;
    const foto = $("#fotoPerfilChat").attr("src", image);
    contenedorChat.attr("data-id", id);
    var chatContainer = $(".chat");
    chatContainer.scrollTop(chatContainer[0].scrollHeight);

    if(type == "chat"){

        let userName = event.currentTarget.dataset.name;
        contenedorChat.attr("data-currentUser", event.currentTarget.dataset.currentuser);
        const name = $("#name").text(userName);
        getMessages(id,type)
    }else{

        getInformationOfChat(id);
        contenedorChat.attr("data-currentUser", "0");
        getMessages(id,type)
    }

    setInterval(() => {
        downloadLastMessage();
    }, 5000);

    
}

function returnToInicialPage(){

    location.reload(true);

}

function getInformationOfChat(data){
    const contenedorChat =  $(".containerChat")
    const formData = new FormData();
    formData.append("matchId", data);  // Añadir el id del usuario al que se le dio like

    // Hacemos la petición para verificar el match
    fetch("apis.php?api=informationProfile", {
        method: "POST",  // Usamos el método POST
        body: formData,  // Enviamos el FormData
    })
    .then(response => response.json())  // Suponemos que la respuesta es JSON
    .then(data => {
        console.log(data)
        data.forEach(element => {
            contenedorChat.attr("data-otherLikeId", element["otherUser"]);
                        const name = $("#name").text(element["Username"]);  
        });
    })
    .catch(error => {
        console.error("Error en la petición:", error);  // Si ocurre un error, lo mostramos
    });


}


function getMessages(data,type){
    const contenedorChat =  $(".containerChat");
    const contenedorMensages =  $(".chat");
    const formData = new FormData();
    formData.append("matchId", data);  // Añadir el id del usuario al que se le dio like

    // Hacemos la petición para verificar el match
    fetch("apis.php?api=getMessages", {
        method: "POST",  // Usamos el método POST
        body: formData,  // Enviamos el FormData
    })
    .then(response => response.json())  // Suponemos que la respuesta es JSON
    .then(data => {
        lastdata = "";
        if(type ==="chat"){
               
                data.forEach(element => {


                        if (lastdata != ""){
                            if(moreFiveMinuts(lastdata,  element["SentAt"])>=5){

                            let divMessage =  $("<div>")
                            divMessage.attr("class", "data")
                            divMessage.text(element["SentAt"]);
                            contenedorMensages.append(divMessage)
                            }
                            

                        }
                     
                        if(parseInt(contenedorChat.attr("data-currentuser")) == parseInt(element["ReceiverUserId"])){
                            
                           let divMessage =  $("<div>")
                           divMessage.attr("class", "chatMessage received")
                           divMessage.text(element["Text"]);
                           divMessage.attr("data-datatime",element["SentAt"] )
                           divMessage.attr("data-MsiD",element["MessageId"] )
                           contenedorMensages.append(divMessage)
                           contenedorChat.attr("data-otherlikeid", element["SenderUserId"])
                        } else{
                            let divMessage =  $("<div>")
                           divMessage.attr("class", "chatMessage sent")
                           divMessage.text(element["Text"]);
                           divMessage.attr("data-datatime",element["SentAt"] )
                           divMessage.attr("data-MsiD",element["MessageId"] )
                           contenedorMensages.append(divMessage)
                           contenedorChat.attr("data-otherlikeid", element["ReceiverUserId"])
                        }
                        lastdata = element["SentAt"];


                })

        }
         
       data-otherlikeiddata-otherlikeid
    })
    .catch(error => {
        console.error("Error en la petición:", error);  // Si ocurre un error, lo mostramos
    });


}


function moreFiveMinuts(lastMessageData, actualMessageData){
    
// Define las dos fechas
let fecha1 = new Date(lastMessageData); // Fecha y hora inicial
let fecha2 = new Date(actualMessageData); // Fecha y hora final

// Calcula la diferencia en milisegundos
let diferenciaMs = fecha2 - fecha1;

// Convierte la diferencia a minutos
let diferenciaMinutos = diferenciaMs / (1000 * 60);

return diferenciaMinutos
}


function sentNewMissage(){

    const contenedorMensages =  $(".chat");
    const inputText = $("#inputMessage")

    let divMessage =  $("<div>")
    divMessage.attr("class", "chatMessage sent")
    divMessage.text(inputText.val());
    divMessage.attr("data-datatime",new Date())

    lastsent = contenedorMensages.children().last().attr("data-datatime");
    if(moreFiveMinuts(lastdata,  new Date()) >=5){

        let divMessage =  $("<div>")
        divMessage.attr("class", "data")
        divMessage.text(formatoFecha());
        contenedorMensages.append(divMessage)
        }

    contenedorMensages.append(divMessage)

    
    const formData = new FormData();
    formData.append("likedUserId",$(".containerChat").attr("data-otherlikeid"));  // Añadir el id del usuario al que se le dio like
    console.log("POLLOOO" + $(".containerChat").attr("data-id"))
    formData.append("matchId",  $(".containerChat").attr("data-id")); 

    formData.append("Text",  inputText.val()); 
    inputText.val("")
    // Hacemos la petición para guardar el like
    fetch("apis.php?api=saveNewMessage", {
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

function formatoFecha() {
    let fecha = new Date();

    // Extraer componentes
    let year = fecha.getFullYear();
    let month = String(fecha.getMonth() + 1).padStart(2, '0'); // Los meses empiezan desde 0
    let day = String(fecha.getDate()).padStart(2, '0');
    let hours = String(fecha.getHours()).padStart(2, '0');
    let minutes = String(fecha.getMinutes()).padStart(2, '0');
    let seconds = String(fecha.getSeconds()).padStart(2, '0');

    // Formato final
    return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
}

function downloadLastMessage() {
    const contenedorChat = $(".containerChat");
    const contenedorMensages = $(".chat");

    const formData = new FormData();
    formData.append("matchId", parseInt(contenedorChat.attr("data-id")));
    formData.append("sentUser", parseInt(contenedorChat.attr("data-otherlikeid")));
    formData.append("lastMessageId", parseInt(contenedorMensages.children().filter('[class="chatMessage received"]').last().attr("data-msid")));

    // Hacemos la petición para verificar el match
    fetch("apis.php?api=downloadLastMessage", {
        method: "POST",
        body: formData,
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log(data)
        if (data && data.length > 0) {
            let lastdata = "";

            data.forEach(element => {
                // Si han pasado más de 5 minutos, añadir un separador
                if (lastdata !== "") {
                    if (moreFiveMinuts(lastdata, element["SentAt"]) >= 5) {
                        const divTime = $("<div>")
                            .attr("class", "data")
                            .text(element["SentAt"]);
                        contenedorMensages.append(divTime);
                    }
                }

                // Agregar el mensaje recibido
                const divMessage = $("<div>")
                    .attr("class", "chatMessage received")
                    .text(element["Text"])
                    .attr("data-datetime", element["SentAt"])
                    .attr("data-msid", element["MessageId"]);
                contenedorMensages.append(divMessage);

                // Actualizar el usuario
                contenedorChat.attr("data-otherlikeid", element["SenderUserId"]);

                lastdata = element["SentAt"];
            });
        } else {
            console.log("No se encontraron nuevos mensajes.");
        }
    })
    .catch(error => {
        console.error("Error en la petición:", error);  // Si ocurre un error, lo mostramos
    });
}

// Función para recuperar más datos (perfiles) y mostrarlos
async function downloadData(id) {
    const formData = new FormData();
    formData.append("UserId", id);  // Añadir el índice a FormData

    try {
        // Hacemos la petición para obtener perfiles
        const response = await fetch("apis.php?api=downloadProfileWithFoto", {
            method: "POST",  // Usamos el método POST
            body: formData  // Enviamos el FormData
        });

        const responseText = await response.text();  // Obtenemos la respuesta como texto
        console.log("Respuesta del servidor:", responseText); 

        // Si la respuesta no está vacía, intentamos parsear el JSON
        if (responseText && responseText.trim() != "") {
            const misDatos = JSON.parse(responseText);  // Intentamos parsear como JSON
            const contenedor = $(".card-profile");  // Contenedor donde se muestran las tarjetas
            console.log(misDatos);
            
            // Si hay perfiles, los mostramos
            if (misDatos.length > 0) {
                misDatos.forEach(usuario => {
                   
                    if(usuario["TotalPoints"] != 0){

                        // contenedor genérico de la carta
                        const newCard = $("<div>").attr("class", "card-profile-view");
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
