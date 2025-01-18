
import { logToServer } from './lib.js';

document.addEventListener('DOMContentLoaded', function() {
    var saveButton = document.getElementById('saveButton');
    saveButton.addEventListener('click', function(e) {
        e.preventDefault();
        validateForm();
    });
});



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

