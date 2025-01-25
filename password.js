$(document).ready(function(){


    const getMailButton = $("#sendMail");
    const getPassButton = $("#sendPass");

    sendMail("jorge.cortes.blanquez02@gmail.com")
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

    showNotification("Email enviado correctamente.", "success");//mostramos noti
   }else{
    showNotification("El email no es correcto.", "error");//mostramos noti
   }
})
.catch(error => {
    console.error('Error:', error);
});

}