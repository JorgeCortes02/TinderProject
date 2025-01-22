
import { logToServer } from './lib.js';

document.addEventListener('DOMContentLoaded', function() {
    var saveButton = document.getElementById('saveButton');
    saveButton.addEventListener('click', function(e) {
        e.preventDefault();
        validateForm();
    });
});

$(document).ready(function(){

    downloadData("x");


    const profileButtom = $("#Profile");
    const confButton = $("#Conf");
    const preferencesButton = $("#menulogout");
    const preferencesDiv = $(".divPreferencesProfile");
    const logOutButton = $("#logOut");

    profileButtom.on("click", function(){

       profileButtom.prop("disabled", true);
       
        const contenedorConf =  $(".fieldsContainer");
        contenedorConf.css("display", "none")
        const contenedorProfile = $(".card-profile2");
        contenedorProfile.css("display", "flex")
      
    })
    confButton.on("click", function(){
        profileButtom.prop("disabled", false);
        const contenedorConf =  $(".fieldsContainer");
        contenedorConf.css("display", "flex")
        const contenedorProfile = $(".card-profile2");
        contenedorProfile.css("display", "none")
    })

    preferencesButton.on("click", function(){
        if(preferencesDiv.css("display") == "flex"){
            preferencesDiv.css("display", "none");

        }else{
            preferencesDiv.css("display", "flex");
        }
        
    });

    logOutButton.on("click", function(){
       
        deleteSession();
        
    });

})


/** Guarda los cambios del perfil al servidor usando AJAX */
function saveProfileChanges() {
    logToServer('saveProfileChanges - Guardando datos...');

    // Campos del formulario
    const firstName = $('#firstName').val();
    const lastName1 = $('#lastName1').val();
    const lastName2 = $('#lastName2').val();
    const userName = $('#userName').val();
    const birthdate = $('#birthdate').val();
    const bio = $('#bio').val();
    const gender = $("input[name='gender']:checked").val(); // Solo guarda la opción seleccionada
    const orientation = $("input[name='orientacion']:checked").val(); // Solo guarda la opción seleccionada
    // const minAge = $('#minAge').val();
    // const maxAge = $('#maxAge').val();
    const latitude = $('#latitude').val();
    const longitude = $('#longitude').val();

    // Envío de datos con AJAX
    $.ajax({
        url: 'profile.php',
        type: 'POST',
        data: {
            action: 'update_session',
            firstName: firstName,
            lastName1: lastName1,
            lastName2: lastName2,
            userName: userName,
            birthDate: birthdate,
            bio: bio,
            gender: gender,
            orientation: orientation,
            latitude: latitude,
            longitude: longitude
        },
        success: function(response) {
            console.log('datos actualizados correctamente');
            logToServer('saveProfileChanges - Solicitud AJAX correcta');
        },
        error: function(error) {
            console.error("Error al actualizar los datos: ", error);
            logToServer("saveProfileChanges - Error en la solicitud AJAX", "ERROR");
        }
    });
}

/** Valida que todos los campos requeridos tengan contenido */
function validateForm() {
    logToServer("Validando form...");
    let isValid = true;
    $('.error-border').removeClass('error-border');
    $('#errorMessage').hide();

    // Si alguno de los inputs requeridos no tiene texto, levanta error
    $('#profileForm input[required], #profileForm textarea[required]').each(function() {
        if ($(this).val().trim() === '') {
            $(this).addClass('error-border');
            isValid = false;
            logToServer('Campos en el form vacios','ERROR');
        }
    });

    // Si gender o orientacion no está seleccionado, levanta error
    if (!$('input[name="gender"]:checked').length) {
        $('input[name="gender"]').closest('label').addClass('error-border');
        isValid = false;
        logToServer("Género invalido",'ERROR');
    }

    if (!$('input[name="orientacion"]:checked').length) {
        $('input[name="orientacion"]').closest('label').addClass('error-border');
        isValid = false;
        logToServer("Orientación invalido",'ERROR');
    }

    if (!isValid) {
        $('#errorMessage').show();
        document.getElementById('scroll').scrollIntoView({ behavior: 'smooth' });
    } else {
        console.log('Entrando a guardar datos');
        logToServer('Campos del form correctos.');
        saveProfileChanges();
    }
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
            const contenedor = $(".card-profile2");  // Contenedor donde se muestran las tarjetas
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

function deleteSession() {
    // Hacemos la petición al servidor sin enviar datos
    fetch("apis.php?api=destroySession", {
        method: "POST"  // Usamos el método POST
    })
        .then(response => {
            if (!response.ok) {  // Verificar si la respuesta fue exitosa
                throw new Error("Error al guardar los datos en el servidor");
            }
            console.log("Solicitud procesada exitosamente");  // Si la respuesta es correcta, mostramos un mensaje
        })
        .catch(error => {
            console.error("Error en la solicitud:", error);  // Si ocurre un error, lo mostramos
        });


        window.location.href = "login.php";   
}
