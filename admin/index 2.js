function deleteSession() {
    // Hacemos la petición al servidor sin enviar datos
    fetch("../apis.php?api=destroySession", {
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


        window.location.href = "/login.php";   
}