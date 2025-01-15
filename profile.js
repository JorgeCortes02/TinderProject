
$(document).ready(function(e) {
    $('.saveButton').on('click', function() {
        e.preventDefault();
        validateForm();
    });
});



/** Guarda los cambios del perfil al servidor usando AJAX */
function saveProfileChanges() {
    console.log("datos guardados!");

    // Campos del formulario
    const firstName = $('#firstName').val();
    const lastName1 = $('#lastName1').val();
    const lastName2 = $('#lastName2').val();
    const userName = $('#userName').val();
    const birthdate = $('#birthdate').val();
    const bio = $('#bio').val();
    const gender = $("input[name='gender']:checked").val(); // Solo guarda la opción seleccionada
    const orientation = $("input[name='orientacion']:checked").val(); // Solo guarda la opción seleccionada
    const minAge = $('#minAge').val();
    const maxAge = $('#maxAge').val();
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
            minAge: minAge,
            maxAge: maxAge,
            latitude: latitude,
            longitude: longitude
        },
        success: function(response) {
            console.log('datos actualizados correctamente');
        },
        error: function(error) {
            console.error("Error al actualizar los datos:", error);
        }
    });
}

/** Valida que todos los campos requeridos tengan contenido */
function validateForm() {
   
    let isValid = true;
    $('.error-border').removeClass('error-border');
    $('#errorMessage').hide();

    // Si alguno de los inputs requeridos no tiene texto, levanta error
    $('#profileForm input[required], #profileForm textarea[required]').each(function() {
        if ($(this).val().trim() === '') {
            $(this).addClass('error-border');
            isValid = false;
        }
    });

    // Si gender o orientacion no está seleccionado, levanta error
    if (!$('input[name="gender"]:checked').length) {
        $('input[name="gender"]').closest('label').addClass('error-border');
        isValid = false;
    }

    if (!$('input[name="orientacion"]:checked').length) {
        $('input[name="orientacion"]').closest('label').addClass('error-border');
        isValid = false;
    }

    if (!isValid) {
        $('#errorMessage').show();
        document.getElementById('scroll').scrollIntoView({ behavior: 'smooth' });
    } else {
        saveProfileChanges();
    }
}

