// =============================================================
// FUNCIONES GLOBALES DE RENDERIZADO
// =============================================================
function renderizarTablaCategorias(categorias) {
    const tablaBody = $('#tabla-categorias-body');
    if (!tablaBody.length) return;
    tablaBody.empty();
    if (!categorias || categorias.length === 0) {
        tablaBody.append('<tr><td colspan="3" class="text-center">No hay categorías registradas.</td></tr>');
        return;
    }
    categorias.forEach(cat => {
        const estadoBadge = cat.activa == 1 ? '<span class="badge bg-success">Activa</span>' : '<span class="badge bg-danger">Inactiva</span>';
        const botonEstadoTexto = cat.activa == 1 ? 'Desactivar' : 'Activar';
        const botonEstadoClase = cat.activa == 1 ? 'btn-danger' : 'btn-success';
        const fila = `<tr><td>${cat.nombre}</td><td>${estadoBadge}</td><td><a href="form_categoria.php?id=${cat.id}" class="btn btn-warning btn-sm">Editar</a> <button class="btn ${botonEstadoClase} btn-sm btn-estado" data-id="${cat.id}" data-estado="${cat.activa}">${botonEstadoTexto}</button></td></tr>`;
        tablaBody.append(fila);
    });
}

function renderizarTablaLugares(lugares) {
    const tablaBody = $('#tabla-lugares-body');
    if (!tablaBody.length) return;
    tablaBody.empty();
    if (!lugares || lugares.length === 0) {
        tablaBody.append('<tr><td colspan="5" class="text-center">No hay lugares registrados.</td></tr>');
        return;
    }
    lugares.forEach(lugar => {
        const fila = `<tr><td>${lugar.nombre_establecimiento || ''}</td><td>${lugar.direccion || ''}</td><td>${lugar.ciudad || ''}</td><td>${lugar.capacidad || ''}</td><td><a href="form_lugar.php?id=${lugar.id}" class="btn btn-warning btn-sm">Editar</a> <button class="btn btn-danger btn-sm btn-eliminar-lugar" data-id="${lugar.id}">Eliminar</button></td></tr>`;
        tablaBody.append(fila);
    });
}

function renderizarTablaEventos(eventos) {
    const tablaBody = $('#tabla-eventos-body');
    if (!tablaBody.length) return;
    
    tablaBody.empty();

    if (!eventos || eventos.length === 0) {
        tablaBody.append('<tr><td colspan="4" class="text-center">No hay eventos registrados.</td></tr>');
        return;
    }

    eventos.forEach(evento => {
        let estadoBadge = '';
        let accionesAdicionales = '';

        // Definimos el estilo y las acciones según el estado del evento
        if (evento.estado === 'Activo') {
            estadoBadge = '<span class="badge bg-success">Activo</span>';
            // Si está activo, mostramos el botón para finalizarlo
            accionesAdicionales = `<button class="btn btn-secondary btn-sm btn-cambiar-estado-evento" data-id="${evento.id}" data-estado-actual="Activo">Finalizar</button>`;
        } else if (evento.estado === 'Finalizado') {
            estadoBadge = '<span class="badge bg-secondary">Finalizado</span>';
            // Si está finalizado, mostramos el botón para reactivarlo
            accionesAdicionales = `<button class="btn btn-success btn-sm btn-cambiar-estado-evento" data-id="${evento.id}" data-estado-actual="Finalizado">Reactivar</button>`;
        } else { // Cancelado
            estadoBadge = '<span class="badge bg-danger">Cancelado</span>';
        }

        const fila = `
            <tr>
                <td>${evento.nombre}</td>
                <td><span class="badge bg-info text-dark">${evento.nombre_categoria}</span></td>
                <td>${estadoBadge}</td>
                <td>
                    <a href="detalle_evento.php?id=${evento.id}" class="btn btn-info btn-sm">Ver</a>
                    <a href="form_evento.php?id=${evento.id}" class="btn btn-warning btn-sm">Editar</a>
                    ${accionesAdicionales}
                </td>
            </tr>
        `;
        tablaBody.append(fila);
    });
}

// =============================================================
// LÓGICA PARA EL MÓDULO DE PARTICIPANTES
// =============================================================

function renderizarTablaParticipantes(participantes) {
    const tablaBody = $('#tabla-participantes-body');
    tablaBody.empty();

    if (!participantes || participantes.length === 0) {
        tablaBody.append('<tr><td colspan="5" class="text-center">No hay participantes registrados en este evento.</td></tr>');
        return;
    }

    participantes.forEach(p => {
        let enlaceComprobante = 'N/A';
        // ¡CAMBIO CLAVE AQUÍ!
        // Usamos la ruta absoluta desde la raíz del sitio web.
        if (p.ruta_comprobante && p.ruta_comprobante !== 'N/A') {
            enlaceComprobante = `<a href="/sistemaEventos/${p.ruta_comprobante}" target="_blank" class="btn btn-outline-info btn-sm">Ver</a>`;
        }

        const fila = `
            <tr>
                <td>${p.nombres} ${p.apellidos}</td>
                <td>${p.cedula}</td>
                <td>${p.email}</td>
                <td><span class="badge bg-success">${p.nombre_entrada}</span></td>
                <td>${enlaceComprobante}</td>
            </tr>
        `;
        tablaBody.append(fila);
    });


}

function renderizarPaginacion(paginacion) {
    const container = $('#pagination-container');
    container.empty();
    if (paginacion.total_paginas <= 1) return;

    let html = '<nav><ul class="pagination">';
    // Botón Anterior
    html += `<li class="page-item ${paginacion.pagina <= 1 ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${paginacion.pagina - 1}">Anterior</a></li>`;
    // Números de página
    for (let i = 1; i <= paginacion.total_paginas; i++) {
        html += `<li class="page-item ${i === paginacion.pagina ? 'active' : ''}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
    }
    // Botón Siguiente
    html += `<li class="page-item ${paginacion.pagina >= paginacion.total_paginas ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${paginacion.pagina + 1}">Siguiente</a></li>`;
    html += '</ul></nav>';
    container.html(html);
}



function renderizarTablaUsuarios(usuarios) {
    const tablaBody = $('#tabla-usuarios-body');
    tablaBody.empty();
    usuarios.forEach(user => {
        const fila = `
            <tr>
                <td>${user.nombres} ${user.apellidos}</td>
                <td>${user.cedula}</td>
                <td><span class="badge bg-secondary">${user.nombre_rol}</span></td>
                <td>
                    <a href="form_usuario.php?id=${user.id}" class="btn btn-warning btn-sm">Editar</a>
                    <button class="btn btn-info btn-sm btn-reset-pass" data-id="${user.id}" data-nombre="${user.nombres} ${user.apellidos}" data-bs-toggle="modal" data-bs-target="#resetPasswordModal">Reset Pass</button>
                    <button class="btn btn-danger btn-sm btn-eliminar-usuario" data-id="${user.id}">Eliminar</button>
                </td>
            </tr>`;
        tablaBody.append(fila);
    });
}


// =============================================================
// CÓDIGO PRINCIPAL
// =============================================================
$(document).ready(function() {

    const basePath = '../../controller/'; // Ruta base para las llamadas AJAX desde view/admin/

    // --- CARGA INICIAL DE LISTAS ---
    if ($('#tabla-categorias-body').length) $.get(basePath + 'CategoriaControlador.php', response => renderizarTablaCategorias(response.data), 'json');
    if ($('#tabla-lugares-body').length) $.get(basePath + 'LugarControlador.php', response => renderizarTablaLugares(response.data), 'json');
    if ($('#tabla-eventos-body').length) $.get(basePath + 'EventoControlador.php', response => renderizarTablaEventos(response.data), 'json');
    if ($('#tabla-usuarios-body').length) $.get(basePath + 'UsuarioControlador.php', response => renderizarTablaUsuarios(response.data), 'json');

    // --- FORMULARIOS SIN ARCHIVOS (Categoría y Lugar) ---
    $('#form-categoria, #form-lugar').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        let redirectUrl = form.is('#form-categoria') ? 'categorias.php' : 'lugares.php';
        $.ajax({
            url: form.attr('action'),
            type: 'POST', data: form.serialize(), dataType: 'json',
            success: r => { if(r.status==='success'){ alert(r.message); window.location.href=redirectUrl; } else { alert('Error: '+r.message); }},
            error: () => alert('Error de comunicación.')
        });
    });

    // --- ¡¡CÓDIGO AÑADIDO PARA EL FORMULARIO DE USUARIO!! ---
    $('#form-usuario').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: r => {
                if (r.status === 'success') {
                    alert(r.message);
                    window.location.href = 'usuarios.php';
                } else {
                    alert('Error: ' + r.message);
                }
            },
            error: () => alert('Error de comunicación.')
        });
    });

    // --- FORMULARIO DE EVENTOS CON ARCHIVO ---
    $('#form-evento').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const submitButton = form.find('button[type="submit"]');
        const originalButtonText = submitButton.html();
        submitButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Guardando...');
        $.ajax({
            url: form.attr('action'),
            type: 'POST', data: new FormData(this), dataType: 'json',
            contentType: false, processData: false,
            success: r => { if(r.status==='success'){ alert(r.message); window.location.href='eventos.php'; } else { alert('Error: '+r.message); }},
            error: () => alert('Error de comunicación al guardar evento.'),
            complete: () => submitButton.prop('disabled', false).html(originalButtonText)
        });
    });

    // --- ACCIONES EN TABLAS ---
    $('#tabla-categorias-body').on('click', '.btn-estado', function() {
        if (!confirm('¿Seguro?')) return;
        $.post(basePath + 'CategoriaControlador.php', { accion: 'estado', id_categoria: $(this).data('id'), estado_actual: $(this).data('estado') }, r => renderizarTablaCategorias(r.data), 'json');
    });
    $('#tabla-lugares-body').on('click', '.btn-eliminar-lugar', function() {
        if (!confirm('¿Seguro?')) return;
        $.post(basePath + 'LugarControlador.php', { accion: 'eliminar', id_lugar: $(this).data('id') }, r => renderizarTablaLugares(r.data), 'json');
    });

    // --- ACCIONES EN detalle_evento.php ---
    if ($('#form-agregar-funcion').length) {
        $(document).on('submit', '.form-eliminar-funcion, #form-agregar-funcion, .form-agregar-ponente, .form-agregar-entrada', function(e){
            e.preventDefault();
            const form = $(this);
            if (form.is('.form-eliminar-funcion') && !confirm('¿Seguro que quieres eliminar?')) return;
            $.ajax({
                url: form.attr('action'),
                type: 'POST', data: form.serialize(), dataType: 'json',
                success: r => { if(r.status==='success'){ location.reload(); } else { alert('Error: '+r.message); }},
                error: () => alert('Error de comunicación.')
            });
        });
    }

    // --- MANEJADOR PARA CAMBIAR ESTADO DE EVENTO ---
    $('#tabla-eventos-body').on('click', '.btn-cambiar-estado-evento', function() {
        const id = $(this).data('id');
        const estado = $(this).data('estado-actual');
        const accion = (estado === 'Activo') ? 'finalizar' : 'reactivar';

        if (!confirm(`¿Estás seguro de que quieres ${accion} este evento?`)) return;

        $.ajax({
            url: '../../controller/EventoControlador.php',
            type: 'POST',
            data: {
                accion: 'cambiar_estado',
                id_evento: id,
                estado_actual: estado
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    // Si todo va bien, simplemente redibujamos la tabla con los nuevos datos
                    renderizarTablaEventos(response.data);
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: () => alert('Ocurrió un error de comunicación.')
        });
    });

    
    // --- LÓGICA PARA EL MÓDULO DE PARTICIPANTES ---
    if ($('#evento-select').length) {
        let idEventoSeleccionado = null;
        let busquedaActual = '';
        const exportBtn = $('#export-excel-btn'); // Variable para el botón de exportar

        function cargarParticipantes(pagina = 1) {
            if (!idEventoSeleccionado) return;
            const tablaBody = $('#tabla-participantes-body');
            tablaBody.html('<tr><td colspan="5" class="text-center">Cargando participantes...</td></tr>');
            
            $.ajax({
                url: '../../controller/ParticipanteControlador.php',
                type: 'GET',
                data: { id_evento: idEventoSeleccionado, busqueda: busquedaActual, pagina: pagina },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        renderizarTablaParticipantes(response.data);
                        renderizarPaginacion(response.paginacion);
                    } else {
                        tablaBody.html('<tr><td colspan="5" class="text-center text-danger">Error al cargar los datos.</td></tr>');
                    }
                },
                error: function() {
                    tablaBody.html('<tr><td colspan="5" class="text-center text-danger">Error de comunicación.</td></tr>');
                }
            });
        }

        $('#evento-select').on('change', function() {
            idEventoSeleccionado = $(this).val();
            busquedaActual = ''; 
            $('#search-participante').val('').show();
            
            // --- Lógica para el botón de exportar ---
            if (idEventoSeleccionado) {
                // Construimos la URL para el reporte y la asignamos al botón
                const urlReporte = `../../controller/ReporteControlador.php?id_evento=${idEventoSeleccionado}`;
                exportBtn.attr('href', urlReporte).show();
            } else {
                exportBtn.hide();
            }
            
            cargarParticipantes();
        });

        $('#search-participante').on('keyup', function() {
            busquedaActual = $(this).val();
            cargarParticipantes(); // La búsqueda siempre vuelve a la página 1
        });

        $('#pagination-container').on('click', 'a.page-link', function(e) {
            e.preventDefault();
            const pagina = $(this).data('page');
            if (pagina && !$(this).parent().hasClass('disabled')) {
                cargarParticipantes(pagina);
            }
        });
    }

    // =============================================================
    // LÓGICA PARA EL MÓDULO DE ESCANEO QR (check-in)
    // =============================================================
    if ($('#qr-reader').length) { // Si estamos en la página de escaneo
        let html5QrCode = null;
        let idEventoSeleccionado = null;
        let busquedaAsistencia = '';

        const resultContainer = $('#scan-result-container');
        const searchInput = $('#search-asistencia');
        const startBtn = $('#start-scan-btn');
        const stopBtn = $('#stop-scan-btn');
        const scannerContainer = $('#scanner-container');
        const participantesContainer = $('#participantes-checkin-container');

        // Función para renderizar la tabla de asistencia
        function renderizarTablaAsistencia(participantes) {
            const tablaBody = $('#tabla-asistencia-body');
            tablaBody.empty();
            if (!participantes || participantes.length === 0) {
                tablaBody.append('<tr><td colspan="4" class="text-center">No se encontraron participantes.</td></tr>');
                return;
            }
            participantes.forEach(p => {
                const estado = p.asistencia === 'Registrado' 
                    ? '<span class="badge bg-success">Registrado</span>'
                    : '<span class="badge bg-warning text-dark">Pendiente</span>';
                
                const accion = p.asistencia !== 'Registrado'
                    ? `<button class="btn btn-success btn-sm btn-marcar-asistencia" data-id-participante="${p.id}">Marcar Asistencia</button>`
                    : '';

                const fila = `
                    <tr id="participante-row-${p.id}">
                        <td>${p.apellidos} ${p.nombres}</td>
                        <td>${p.cedula}</td>
                        <td>${estado}</td>
                        <td>${accion}</td>
                    </tr>
                `;
                tablaBody.append(fila);
            });
        }

        // ¡NUEVA FUNCIÓN PARA RENDERIZAR PAGINACIÓN DE ASISTENCIA!
        function renderizarPaginacionAsistencia(paginacion) {
            const container = $('#pagination-asistencia-container');
            container.empty();
            if (!paginacion || paginacion.total_paginas <= 1) return;

            let html = '<nav><ul class="pagination">';
            for (let i = 1; i <= paginacion.total_paginas; i++) {
                html += `<li class="page-item ${i === paginacion.pagina ? 'active' : ''}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
            }
            html += '</ul></nav>';
            container.html(html);
        }
        
        // Función para cargar la lista de participantes (ACTUALIZADA)
        function cargarAsistencia(pagina = 1) {
            if (!idEventoSeleccionado) return;
            participantesContainer.show();
            $('#tabla-asistencia-body').html('<tr><td colspan="4" class="text-center">Cargando...</td></tr>');
            
            $.get('../../controller/CheckinControlador.php', { id_evento: idEventoSeleccionado, busqueda: busquedaAsistencia, pagina: pagina }, function(response) {
                if(response.status === 'success') {
                    renderizarTablaAsistencia(response.data);
                    renderizarPaginacionAsistencia(response.paginacion); // Llamamos a la nueva función
                }
            }, 'json');
        }

        // Cuando se selecciona un evento
        $('#evento-checkin-select').on('change', function() {
            idEventoSeleccionado = $(this).val();
            searchInput.prop('disabled', false);
            startBtn.prop('disabled', false);
            busquedaAsistencia = '';
            searchInput.val('');
            cargarAsistencia();
        });

        // Cuando se busca en la barra
        searchInput.on('keyup', function() {
            busquedaAsistencia = $(this).val();
            cargarAsistencia();
        });

        // ¡NUEVO MANEJADOR PARA LOS BOTONES DE PAGINACIÓN!
        $('#pagination-asistencia-container').on('click', 'a.page-link', function(e) {
            e.preventDefault();
            const pagina = $(this).data('page');
            if (pagina && !$(this).parent().hasClass('disabled')) {
                cargarAsistencia(pagina);
            }
        });

        // Botón Iniciar Cámara
        startBtn.on('click', function() {
            scannerContainer.slideDown();
            html5QrCode = new Html5Qrcode("qr-reader");
            $(this).hide();
            stopBtn.show();
            html5QrCode.start({ facingMode: "environment" }, { fps: 10, qrbox: { width: 250, height: 250 } }, onScanSuccess, () => {});
        });

        // Botón Detener Cámara
        stopBtn.on('click', function() {
            if (html5QrCode) {
                html5QrCode.stop().then(() => {
                    scannerContainer.slideUp();
                    $(this).hide();
                    startBtn.show();
                });
            }
        });

        // Al escanear un QR
        function onScanSuccess(decodedText, decodedResult) {
            html5QrCode.pause();
            verificarAsistencia(decodedText, idEventoSeleccionado, true);
        }

        // Al marcar asistencia manualmente
        $('#tabla-asistencia-body').on('click', '.btn-marcar-asistencia', function() {
            const idParticipante = $(this).data('id-participante');
            verificarAsistencia(idParticipante, idEventoSeleccionado, false);
        });

        // Función central para verificar (por QR o manual)
        function verificarAsistencia(valor, idEvento, esCedula) {
            const postData = { id_evento: idEvento };
            if (esCedula) {
                postData.cedula = valor;
            } else {
                postData.id_participante = valor;
            }

            $.post('../../controller/CheckinControlador.php', postData, function(response) {
                const alertClass = response.status === 'success' ? 'alert-success' : 'alert-danger';
                let content = `<h5>${response.message}</h5>`;
                if (response.participante) content += `<p class="mb-0">${response.participante}</p>`;
                resultContainer.html(`<div class="alert ${alertClass}">${content}</div>`);

                // Si fue exitoso, actualizamos la tabla
                if (response.status === 'success') {
                    cargarAsistencia();
                }

                if (esCedula) {
                    setTimeout(() => { if (html5QrCode) html5QrCode.resume(); }, 2000);
                }
            }, 'json');
        }
    }

    // --- LÓGICA PARA EL GRÁFICO DEL DASHBOARD ---
    if ($('#participantesPorEventoChart').length) {
        $.ajax({
            url: '../../controller/DashboardControlador.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                const ctx = document.getElementById('participantesPorEventoChart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar', // Tipo de gráfico: barras
                    data: {
                        labels: response.labels, // Nombres de los eventos
                        datasets: [{
                            label: 'Nº de Participantes',
                            data: response.data, // Cantidad de participantes
                            backgroundColor: 'rgba(78, 115, 223, 0.8)',
                            borderColor: 'rgba(78, 115, 223, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 10 // O ajusta según necesites
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false // Ocultamos la leyenda para un look más limpio
                            }
                        }
                    }
                });
            },
            error: function() {
                // Manejo de error si no se pueden cargar los datos del gráfico
                $('#participantesPorEventoChart').parent().html('<p class="text-center text-danger">No se pudieron cargar los datos del gráfico.</p>');
            }
        });
    }

    // --- LÓGICA PARA GESTIÓN DE USUARIOS ---
    if ($('#tabla-usuarios-body').length) {
        // Carga inicial de usuarios
        $.get('../../controller/UsuarioControlador.php', response => renderizarTablaUsuarios(response.data), 'json');

        // Envío del formulario de creación/edición
        $('#form-usuario').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: r => {
                    if (r.status === 'success') {
                        alert(r.message);
                        window.location.href = 'usuarios.php';
                    } else {
                        alert('Error: ' + r.message);
                    }
                },
                error: () => alert('Error de comunicación.')
            });
        });

        // Abrir el modal para resetear contraseña
        $('#tabla-usuarios-body').on('click', '.btn-reset-pass', function() {
            const id = $(this).data('id');
            const nombre = $(this).data('nombre');
            $('#reset-id-usuario').val(id);
            $('#nombre-usuario-reset').text(nombre);
        });

        // --- ¡BLOQUE CORREGIDO! ---
        // Variable para guardar el mensaje de éxito temporalmente
        let resetSuccessMessage = '';

        // Envío del formulario del modal de reseteo
        $('#form-reset-pass').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            $.ajax({
                url: '../../controller/UsuarioControlador.php',
                type: 'POST',
                data: form.serialize(),
                dataType: 'json',
                success: r => {
                    if (r.status === 'success') {
                        // 1. Guardamos el mensaje de éxito en nuestra variable.
                        resetSuccessMessage = r.message;
                        // 2. Limpiamos y le decimos al modal que se oculte. NO mostramos el alert aquí.
                        form[0].reset();
                        $('#resetPasswordModal').modal('hide');
                    } else {
                        alert('Error: ' + r.message);
                    }
                },
                error: () => alert('Error de comunicación.')
            });
        });

        // Evento que se dispara DESPUÉS de que el modal se haya ocultado completamente
        $('#resetPasswordModal').on('hidden.bs.modal', function() {
            // 3. Si tenemos un mensaje de éxito guardado, lo mostramos ahora que la pantalla está limpia.
            if (resetSuccessMessage) {
                alert(resetSuccessMessage);
                resetSuccessMessage = ''; // Limpiamos la variable para el próximo uso.
            }
        });
        // --- FIN DEL BLOQUE CORREGIDO ---

        // Eliminar usuario
        $('#tabla-usuarios-body').on('click', '.btn-eliminar-usuario', function() {
            if (!confirm('¿Seguro que quieres eliminar a este usuario?')) return;
            const id = $(this).data('id');
            $.post('../../controller/UsuarioControlador.php', {
                accion: 'eliminar',
                id_usuario: id
            }, r => {
                if (r.status === 'success') {
                    // Recargamos la tabla para reflejar el cambio
                    $.get('../../controller/UsuarioControlador.php', response => renderizarTablaUsuarios(response.data), 'json');
                } else {
                    alert('Error: ' + r.message);
                }
            }, 'json');
        });
    }

});