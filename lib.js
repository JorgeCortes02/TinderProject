function logToServer (mensaje,tipo = 'INFO') {
    // Logica para enviar mensaje al servidor
    let pag = window.location.href;

    fetch('apis.php?api=logEvent'),{
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'//contenido como JSON
        },
        body: JSON.stringify({
            mensajeLog: mensaje,
            tipoLog: tipo,
            pagLog: pag
        })
    }.then(response => {
        if (!response.ok) {  // Verificar si la respuesta fue exitosa
            throw new Error("Error al pasar los datos Log");
        }
        console.log("Datos Log pasados exitosamente");  // Si la respuesta es correcta, mostramos un mensaje
    })
    .catch(error => {
        console.error("Error en la solicitud datos Log:", error);  // Si ocurre un error, lo mostramos
    });
}