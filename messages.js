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
                           contenedorChat.attr("data-otherUserId", element["SenderUserId"])
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
                contenedorChat.attr("data-otherUserId", element["SenderUserId"]);

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