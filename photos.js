$(document).ready(function () {

    // Estado global para gestionar las fotos y el usuario
    let photoState = {
        photos: [], // URLs de fotos cargadas
        userID: null // ID del usuario
    };

    // Paso 1: cargar las imágenes
    (async () => {
        photoState.userID = await getUserID();
        if (photoState.userID) {
            await downloadImages(photoState.userID);
        } else {
            console.error("No se pudo obtener el userID.");
        }
    })();

    // Paso 2: obtener el id del usuario
    async function getUserID() {
        try {
            const response = await fetch("apis.php?api=getUserID", {
                method: "POST"
            });

            const data = await response.json();
            if (data.userID) {
                console.log("UserID obtenido:", data.userID);
                return data.userID;
            } else {
                console.error("Error al obtener el userID:", data.error);
            }
        } catch (error) {
            console.error("Error en la solicitud:", error);
        }
    }

    // Paso 3: descargar las imágenes
    async function downloadImages(userID) {
        try {
            const formData = new FormData();
            formData.append('userID', userID);

            const response = await fetch("apis.php?api=downloadImages", {
                method: "POST",
                body: formData
            });

            const responseData = await response.json();
            console.log("Respuestas de fotos:", responseData);

            if (responseData && Array.isArray(responseData)) {
                photoState.photos = responseData; // Actualizar el estado global
                renderPhotoContainers();
            }
        } catch (e) {
            console.error("Error al parsear JSON:", e);
        }
    }

    // Paso 4: renderizar los contenedores de fotos basados en el estado
    function renderPhotoContainers() {
        const photoContainer = $('#containerPhotos');
        // Limpiar el contenedor
        photoContainer.empty();

        // Limitar el número de paneles
        const maxPanels = 6;

        for (let i = 0; i < maxPanels; i++) {
            const newPhotoPanel = $("<div>").attr("class", "newPhotoPanel").attr("data-idPanel", i);

            if (i < photoState.photos.length) {
                // Si hay una foto para este panel, añadirla
                const photo = $("<img>").attr("src", photoState.photos[i]).attr("alt", "Foto " + (i + 1));
                const closeImg = $("<img>")
                    .attr("src", "images/close.png")
                    .attr("alt", "Borrar foto " + (i + 1))
                    .addClass("close-btn")
                    .css({ display: photoState.photos.length > 1 ? "block" : "none" }) // Ocultar si solo hay una foto
                    .on("click", () => removePhoto(i));

                newPhotoPanel.append(photo).append(closeImg).addClass("noAdd");

                // Añadir texto "Foto principal" en el primer panel
                if (i === 0) {
                    const mainPhotoText = $("<div>")
                        .addClass("main-photo-text")
                        .text("Foto principal");

                    newPhotoPanel.append(mainPhotoText);
                }
            } else {
                // Panel vacío
                newPhotoPanel.addClass("yesAdd");
                if (i === photoState.photos.length) {
                    // Primer panel vacío obtiene el botón de añadir
                    const addImg = $("<img>")
                        .attr("src", "images/add.png")
                        .attr("alt", "Añadir foto")
                        .addClass("add-img")
                        .css({ cursor: "pointer" })
                        .on("click", () => addNewPhoto(i));

                    newPhotoPanel.append(addImg);
                }
            }

            photoContainer.append(newPhotoPanel);
        }
    }

    // Paso 5: eliminar una foto
    async function removePhoto(index) {
        const photoURL = photoState.photos[index];
        const formData = new FormData();
        formData.append('userID', photoState.userID);
        formData.append('imgSrc', photoURL);

        try {
            const response = await fetch('apis.php?api=deletePhoto', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            if (data.success) {
                showNotification("Foto eliminada","warning");
                console.log(`Foto eliminada correctamente: ${photoURL}`);
                photoState.photos.splice(index, 1); // Actualizar el estado global
                renderPhotoContainers(); // Re-renderizar los paneles
            } else {
                console.error(`Error al eliminar la foto: ${data.message}`);
            }
        } catch (error) {
            console.error('Error en la solicitud de eliminación:', error);
        }
    }

    // Paso 6: añadir una foto
    function addNewPhoto(panelIndex) {
        const fileInput = $("<input>")
            .attr({
                type: "file",
                id: "fileInput",
                accept: ".jpg, .jpeg, .png, .webp",
            })
            .css({ display: "none" });

        $("body").append(fileInput);
        fileInput.click();

        fileInput.on("change", async function (e) {
            const file = e.target.files[0];
            if (!file) {
                console.log("No se seleccionó ningún archivo.");
                fileInput.remove();
                return;
            }

            const validExtensions = ["jpg", "jpeg", "png", "webp"];
            const fileExtension = file.name.split(".").pop().toLowerCase();
            if (!validExtensions.includes(fileExtension)) {
                alert("Formato de archivo no permitido.");
                fileInput.remove();
                return;
            }

            const formData = new FormData();
            formData.append("userID", photoState.userID);
            formData.append("file", file);

            try {
                const response = await fetch("apis.php?api=uploadPhoto", {
                    method: "POST",
                    body: formData,
                });

                const result = await response.json();
                if (result.success) {
                    showNotification("Foto añadida","info");
                    photoState.photos.push(result.fileURL); // Actualizar el estado global
                    renderPhotoContainers(); // Re-renderizar los paneles
                } else {
                    alert("Error al subir la foto: " + result.message);
                }
            } catch (error) {
                console.error("Error al subir la foto:", error);
            } finally {
                fileInput.remove();
            }
        });
    }
});