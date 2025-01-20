
import { logToServer } from './lib.js';

document.addEventListener('DOMContentLoaded', function() {
    var saveButton = document.getElementById('createProfileButton');
    saveButton.addEventListener('click', function(e) {
        e.preventDefault();
        validateForm();
    });
});



/** Guarda los cambios del perfil al servidor usando AJAX */
function saveProfileChanges() {
    logToServer('creando datos...');

    // Campos del formulario
    const email = $('#email').val();
    const passw = $('#password').val();
    const firstName = $('#firstName').val();
    const lastName1 = $('#lastName1').val();
    const lastName2 = $('#lastName2').val();
    const userName = $('#userName').val();
    const birthdate = $('#birthdate').val();
    const bio = $('#bio').val();
    const gender = $("input[name='gender']:checked").val(); // Solo guarda la opción seleccionada
    const orientation = $("input[name='orientacion']:checked").val(); // Solo guarda la opción seleccionada
    const minAge = 18;
    const maxAge = 99;
    const latitude = $('#latitude').val();
    const longitude = $('#longitude').val();

    // Envío de datos con AJAX
    $.ajax({
        url: 'register.php',
        type: 'POST',
        data: {
            action: 'create_user',
            email: email,
            passw: passw,
            firstName: firstName,
            lastName1: lastName1,
            lastName2: lastName2,
            userName: userName,
            birthDate: birthdate,
            bio: bio,
            gender: gender,
            orientation: orientation,
            latitude: latitude,
            longitude: longitude,
            minAge: minAge,
            maxAge: maxAge
        },
        success: function(response) {
            console.log('datos actualizados correctamente');
            logToServer('Solicitud AJAX correcta, enviando petición de crear usuario al servidor,,,');
        },
        error: function(error) {
            console.error("Error al actualizar los datos: ", error);
            logToServer("Error en la solicitud AJAX para crear usaurio", "ERROR");
        }
    });
}

/** Valida que todos los campos requeridos tengan contenido */
function validateForm() {
    logToServer("Validando form...");
    let isValid = true;
    $('.error-border').removeClass('error-border');
    $('#errorMessage').hide();
    $('#errorPassword').hide();
    $('#errorMail').hide();

    const email = $('#email').val();
    const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    
    if (!emailPattern.test(email)) {
        $('#email').addClass('error-border');
        $('#errorMail').show(); 
        document.getElementById('scroll').scrollIntoView({ behavior: 'smooth' });
        isValid = false;
        logToServer("Correo electrónico inválido", "ERROR");
    }

    // Si alguno de los inputs requeridos no tiene texto, levanta error
    $('#registerForm input[required], #registerForm textarea[required]').each(function() {
        if ($(this).val().trim() === '') {
            $(this).addClass('error-border');
            isValid = false;
            logToServer('Campos en el form vacios','ERROR');
        }
    });

    // Si gender o orientacion no está seleccionado, levanta error
    if (!$('input[name="gender"]:checked').length) {
        $('h3:contains("Género")').addClass('error-border');
        isValid = false;
        logToServer("Género invalido",'ERROR');
    }

    if (!$('input[name="orientacion"]:checked').length) {
        $('h3:contains("Orientación")').addClass('error-border');
        isValid = false;
        logToServer("Orientación invalido",'ERROR');
    }
    
    if ($('#password').val() !== $('#password2').val()) {
        isValid = false;
        $('#errorPassword').show();  // Mostrar el mensaje de error
        $('#password').addClass('error-border');  // Marcar el campo de contraseña con error
        $('#password2').addClass('error-border');  // Marcar el campo de confirmación con error
        document.getElementById('scroll').scrollIntoView({ behavior: 'smooth' });
        logToServer("Error en el registro, las contraseñas no coinciden", 'ERROR');
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

