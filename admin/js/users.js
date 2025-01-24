$(document).ready(function () {
    let currentPage = 1; // Página inicial
    // Cargar los usuarios de la primera página al cargar la página
    loadUsers(currentPage);z

    // Función para cargar usuarios
    function loadUsers(page) {
        $.ajax({
            url: 'rsc/getAllUsers.php', // Archivo PHP para cargar usuarios
            type: 'GET',
            data: { page: page }, // Enviar el número de página
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    renderUsers(response.users); // Renderizar usuarios
                    updatePaginator(response.currentPage, response.totalPages); // Actualizar el paginador
                } else {
                    alert(response.message);
                }
            },
            error: function () {
                alert('Error al cargar los usuarios.');
            }
        });
    }

    // Función para renderizar usuarios en el DOM
    function renderUsers(users) {
        const $table = $('#listedUsers');
        $table.empty();

        
        // cabecera de la tabla (tr con th)
        const $header = $('<tr>');
        $header.append('<th>Id</th>');
        $header.append('<th>Username</th>');
        $header.append('<th>LastName</th>');
        $header.append('<th>Email</th>');
        $header.append('<th>CreateAt</th>');
        $table.append($header);

        // crear las filas de la tabla con los datos de los usuarios
        users.forEach(user => {
            const $row = $('<tr>'); // Crear una fila

            // Agregar un evento de clic a la fila
            $row.on('click', function () {
                // Redirige a una URL usando el IdUser como parámetro
                getUserData(user.IdUser);
            });

            // Crear celdas de la fila
            $row.append(`<td>${user.IdUser}</td>`);
            $row.append(`<td>${user.UserName}</td>`); 
            $row.append(`<td>${user.LastName1}</td>`); 
            $row.append(`<td>${user.Email}</td>`); 
            $row.append(`<td>${user.CreateAt}</td>`);

            $table.append($row); // Agregar la fila a la tabla
        });
    }

    // Función para actualizar el paginador
    function updatePaginator(currentPage, totalPages) {
        const $paginator = $('#paginator');
        $paginator.empty();
    
        // Número máximo de páginas visibles en el paginador
        const maxVisiblePages = 5;
        const startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
        const endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
    
        // Botón para ir a la primera página
        if (currentPage > 1) {
            $paginator.append(`<button class="paginate-btn" data-page="1"><<</button>`);
        }
    
        // Botón "Anterior"
        if (currentPage > 1) {
            $paginator.append(`<button class="paginate-btn" data-page="${currentPage - 1}"><</button>`);
        }
    
        // Botones para las páginas
        for (let i = startPage; i <= endPage; i++) {
            const activeClass = i === currentPage ? 'active' : '';
            $paginator.append(`<button class="paginate-btn ${activeClass}" data-page="${i}">${i}</button>`);
        }
    
        // Botón "Siguiente"
        if (currentPage < totalPages) {
            $paginator.append(`<button class="paginate-btn" data-page="${currentPage + 1}">></button>`);
        }
    
        // Botón para ir a la última página
        if (currentPage < totalPages) {
            $paginator.append(`<button class="paginate-btn" data-page="${totalPages}">>></button>`);
        }
    }
    
    // Evento para manejar clics en los botones de paginación
    $(document).on('click', '.paginate-btn', function () {
        const page = $(this).data('page');
        loadUsers(page);
    });

    // función para recoger los datos del usuario
    function getUserData(userId) {
        $.ajax({
            url: 'rsc/getDataUser.php', // Archivo PHP para obtener datos de un usuario
            type: 'GET',
            data: { id: userId }, // Enviar el ID del usuario
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    // Redirigir con los datos recibidos como parámetros (opcional)
                    window.location.href = `/admin/users.php?id=${userId}`;
                } else {
                    alert(response.message);
                }
            },
            error: function () {
                alert('Error al obtener los datos del usuario.');
            }
        });
    }

    



});