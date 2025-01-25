$(document).ready(function(){


    const getMailButton = $("#sendMail");
    const getPassButton = $("#sendPass");
    
    
    getMailButton.on("click", function(){

        const mailTextBox = $("#email");

        let mail = mailTextBox.val();
        console.log(mail)
        sendMail(mail);

    })

    getPassButton.on("click", function(){

       // Obtener los valores de las contraseñas
       const newPassword = $('#password').val();
       const confirmPassword = $('#password2').val();

       // Expresión regular para validar la contraseña
       const passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d]{8,}$/;

       // Verificar que las contraseñas coinciden
       if (newPassword !== confirmPassword) {
           $('#error-messagePass').text("Las contraseñas no coinciden.").show();
           return;
       }

       // Validar que la nueva contraseña cumpla con los requisitos
       if (!passwordPattern.test(newPassword)) {
           $('#error-messagePass').text("La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula y un número.").show();
           return;
       }

       // Si todo está bien, ocultar el mensaje de error y proceder con el envío
       $('#error-messagePass').hide();

       savePass(newPassword);

    })
})


function sendMail(email){
// Hacer un fetch usando el valor de 'token'
const formData = new FormData();
formData.append("email", email);  
fetch('apis.php?api=generateLinkForChangePass', {
    method: 'POST',
  
    body: formData
})
.then(response => response.text())  // Asumimos que la respuesta es un texto
.then(data => {
    console.log(data)
    console.log(typeof(data))
   if(data == "1"){

    showNotification("Email enviado correctamente.", "success");
    setTimeout(() => {
        window.location.href = 'login.php';
    }, 4000);
   }else{
    showNotification("El email no es correcto.", "error");//mostramos noti
   }
})
.catch(error => {
    console.error('Error:', error);
});

}


function savePass(newPassword){
    console.log(typeof(newPassword))

    const formData = new FormData();
    
     formData.append("newPass", newPassword);  


    // Hacemos la petición para guardar el like
    fetch("apis.php?api=saveANewPass", {
        method: "POST",  // Usamos el método POST
        body: formData  // Enviamos el FormData
    })
        .then(response => {
            if (!response.ok) {  // Verificar si la respuesta fue exitosa
                showNotification("Error al guardar la contraseña", "error");
                throw new Error("Error al guardar los datos en el servidor");
               
            }else{
                showNotification("Contraseña guardada correctamente", "succes");
            }
           
        })
        .catch(error => {
            console.error("Error en la solicitud:", error);  // Si ocurre un error, lo mostramos
        });
} 