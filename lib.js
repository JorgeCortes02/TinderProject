export function logToServer(mensaje, tipo = 'INFO') {
    let pag = window.location.href;

    console.log('pagina =' + pag);
    fetch('apis.php?api=logEvent', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            mensajeLog: mensaje,
            tipoLog: tipo,
            pagLog: pag || 'Pagina no especificada'
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error("Error al pasar los datos Log");
        }
        console.log("Datos Log pasados exitosamente");
    })
    .catch(error => {
        console.error("Error en la solicitud de datos Log:", error);
    });
}