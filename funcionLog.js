function registrarLogEnServidor(mensaje, tipo = 'INFO') {
    fetch('apis.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({
            mensaje: mensaje,
            tipo: tipo
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            console.log('Log registrado correctamente');
        } else {
            console.error('Error al registrar el log: ', data.message);
        }
    })
    .catch(error => {
        console.error('Error en la solicitud: ', error);
    });
}
