// =============================================================
// FUNCIONES GLOBALES DE RENDERIZADO
// =============================================================

function escapeHTML(str) {
    if (str === null || str === undefined) {
        return '';
    }
    return str.toString().replace(/[&<>"']/g, function(match) {
        return {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;'
        }[match];
    });
}



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
        const fila = `<tr><td>${escapeHTML(cat.nombre)}</td><td>${estadoBadge}</td><td><a href="form_categoria.php?id=${cat.id}" class="btn btn-warning btn-sm">Editar</a> <button class="btn ${botonEstadoClase} btn-sm btn-estado" data-id="${cat.id}" data-estado="${cat.activa}">${botonEstadoTexto}</button></td></tr>`;
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
        const fila = `<tr>
            <td>${escapeHTML(lugar.nombre_establecimiento || '')}</td>
            <td>${escapeHTML(lugar.direccion || '')}</td>
            <td>${escapeHTML(lugar.ciudad || '')}</td>
            <td>${escapeHTML(lugar.capacidad || '')}</td>
            <td>
            <a href="form_lugar.php?id=${lugar.id}" class="btn btn-warning btn-sm">Editar</a>
            <button class="btn btn-danger btn-sm btn-eliminar-lugar" data-id="${lugar.id}">Eliminar</button>
            </td>
        </tr>`;
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
            <td>${escapeHTML(evento.nombre)}</td>
            <td><span class="badge bg-info text-dark">${escapeHTML(evento.nombre_categoria)}</span></td>
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
            <td>${escapeHTML(p.nombres)} ${escapeHTML(p.apellidos)}</td>
            <td>${escapeHTML(p.cedula)}</td>
            <td>${escapeHTML(p.email)}</td>
            <td><span class="badge bg-success">${escapeHTML(p.nombre_entrada)}</span></td>
            <td>${enlaceComprobante}</td>
            </tr>
        `;
        tablaBody.append(fila);
    });


}

function renderizarPaginacionParticipantes(paginacion) {
    const container = $('#pagination-container');
    container.empty();
    if (paginacion.total_paginas <= 1) return;

    let html = '<nav><ul class="pagination">';
    const { pagina: current_page, total_paginas } = paginacion;

    // Botón Anterior
    html += `<li class="page-item ${current_page <= 1 ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${current_page - 1}">Anterior</a></li>`;

    // --- Lógica de paginación inteligente ---
    const pagesToShow = [];
    const window = 2;

    pagesToShow.push(1);
    if (current_page > window + 2) pagesToShow.push('...');
    for (let i = Math.max(2, current_page - window); i <= Math.min(total_paginas - 1, current_page + window); i++) pagesToShow.push(i);
    if (current_page < total_paginas - window - 1) pagesToShow.push('...');
    if (total_paginas > 1) pagesToShow.push(total_paginas);

    const uniquePages = [...new Set(pagesToShow)];
    uniquePages.forEach(page => {
        if (page === '...') {
            html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
        } else {
            html += `<li class="page-item ${page === current_page ? 'active' : ''}"><a class="page-link" href="#" data-page="${page}">${page}</a></li>`;
        }
    });

    // Botón Siguiente
    html += `<li class="page-item ${current_page >= total_paginas ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${current_page + 1}">Siguiente</a></li>`;
    html += '</ul></nav>';
    container.html(html);
}


function renderizarTablaUsuarios(usuarios) {
    const tablaBody = $('#tabla-usuarios-body');
    tablaBody.empty();
    usuarios.forEach(user => {
        const fila = `
            <tr>
            <td>${escapeHTML(user.nombres)} ${escapeHTML(user.apellidos)}</td>
            <td>${escapeHTML(user.cedula)}</td>
            <td><span class="badge bg-secondary">${escapeHTML(user.nombre_rol)}</span></td>
            <td>
                <a href="form_usuario.php?id=${user.id}" class="btn btn-warning btn-sm">Editar</a>
                <button class="btn btn-info btn-sm btn-reset-pass" data-id="${user.id}" data-nombre="${escapeHTML(user.nombres)} ${escapeHTML(user.apellidos)}" data-bs-toggle="modal" data-bs-target="#resetPasswordModal">Reset Pass</button>
                <button class="btn btn-danger btn-sm btn-eliminar-usuario" data-id="${user.id}">Eliminar</button>
            </td>
            </tr>`;
        tablaBody.append(fila);
    });
}


// =============================================================
// SECCIÓN PARA GESTIÓN DE BECADOS
// =============================================================

// --- FUNCIÓN PRINCIPAL PARA CARGAR BECADOS ---
function cargarBecados(searchTerm = '', page = 1) {
    const tablaBody = $('#tabla-becados-body');
    tablaBody.html('<tr><td colspan="6" class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Cargando...</span></div></td></tr>');

    $.get(basePath + 'BecadoControlador.php', { accion: 'listar', search: searchTerm, page: page }, response => {
        if (response.status === 'success') {
            renderizarTablaBecados(response.data);
            renderizarPaginacionBecados(response.pagination);
        } else {
            tablaBody.html('<tr><td colspan="6" class="text-center text-danger">Error al cargar los becados.</td></tr>');
        }
    }, 'json');
}

// --- FUNCIÓN PARA RENDERIZAR LA TABLA ---
function renderizarTablaBecados(becados) {
    const tablaBody = $('#tabla-becados-body');
    tablaBody.empty();

    if (!becados || becados.length === 0) {
        tablaBody.append('<tr><td colspan="6" class="text-center">No se encontraron resultados.</td></tr>');
        return;
    }

    becados.forEach(b => {
        const estadoBadge = b.estado === 'Activo' ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>';
        const botonTexto = b.estado === 'Activo' ? 'Desactivar' : 'Activar';
        const botonClase = b.estado === 'Activo' ? 'btn-warning' : 'btn-success';

        const fila = `
            <tr>
                <td>${b.cedula}</td>
                <td>${b.nombres_apellidos}</td>
                <td>${b.programa}</td>
                <td><strong>${b.ateneas_cursadas} / 3</strong></td>
                <td>${estadoBadge}</td>
                <td>
                    <button class="btn ${botonClase} btn-sm btn-cambiar-estado-becado" data-id="${b.id}" data-estado="${b.estado}">${botonTexto}</button>
                </td>
            </tr>
        `;
        tablaBody.append(fila);
    });
}

// --- FUNCIÓN PARA RENDERIZAR LA PAGINACIÓN ---
function renderizarPaginacionBecados(pagination) {
    const { total_records, current_page, total_pages, limit } = pagination; 
    const paginationControls = $('#pagination-controls');
    const paginationInfo = $('#pagination-info');
    paginationControls.empty();
    
    if (total_records === 0) {
        paginationInfo.text('No hay registros');
        return;
    }

    const startRecord = (current_page - 1) * limit + 1;
    const endRecord = Math.min(startRecord + limit - 1, total_records);
    paginationInfo.text(`Mostrando ${startRecord}-${endRecord} de ${total_records} registros`);

    if (total_pages <= 1) return;

    // Lógica para botones Anterior y Siguiente
    const prevDisabled = current_page === 1 ? 'disabled' : '';
    const nextDisabled = current_page === total_pages ? 'disabled' : '';

    paginationControls.append(`<li class="page-item ${prevDisabled}"><a class="page-link" href="#" data-page="${current_page - 1}">Anterior</a></li>`);
    
    // --- Lógica de paginación inteligente ---
    const pagesToShow = [];
    const window = 2; // Cantidad de páginas a mostrar alrededor de la actual

    pagesToShow.push(1); // Siempre mostrar la primera página

    if (current_page > window + 2) {
        pagesToShow.push('...');
    }

    for (let i = Math.max(2, current_page - window); i <= Math.min(total_pages - 1, current_page + window); i++) {
        pagesToShow.push(i);
    }

    if (current_page < total_pages - window - 1) {
        pagesToShow.push('...');
    }
    
    if (total_pages > 1) {
        pagesToShow.push(total_pages); // Siempre mostrar la última página
    }

    // Renderizar los botones de página
    const uniquePages = [...new Set(pagesToShow)]; // Eliminar duplicados por si acaso
    uniquePages.forEach(page => {
        if (page === '...') {
            paginationControls.append('<li class="page-item disabled"><span class="page-link">...</span></li>');
        } else {
            paginationControls.append(`<li class="page-item ${page === current_page ? 'active' : ''}"><a class="page-link" href="#" data-page="${page}">${page}</a></li>`);
        }
    });

    paginationControls.append(`<li class="page-item ${nextDisabled}"><a class="page-link" href="#" data-page="${current_page + 1}">Siguiente</a></li>`);
}



function showNotification(message, type = 'success') {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    // Reemplazamos los saltos de línea (\n) por etiquetas <br> para que se vean bien en HTML
    const formattedMessage = message.replace(/\n/g, '<br>');
    
    const notification = $(`
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert" style="min-width: 300px;">
            ${formattedMessage}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `);

    $('#notification-area').append(notification);

    // Hacemos que la notificación desaparezca sola después de 5 segundos
    setTimeout(() => {
        notification.alert('close');
    }, 5000);
}


// =============================================================
// CÓDIGO PRINCIPAL
// =============================================================
const basePath = '../../controller/';

$(document).ready(function() {

    // --- CARGA INICIAL DE LISTAS ---
    if ($('#tabla-categorias-body').length) $.get(basePath + 'CategoriaControlador.php', response => renderizarTablaCategorias(response.data), 'json');
    if ($('#tabla-lugares-body').length) $.get(basePath + 'LugarControlador.php', response => renderizarTablaLugares(response.data), 'json');
    if ($('#tabla-eventos-body').length) $.get(basePath + 'EventoControlador.php', response => renderizarTablaEventos(response.data), 'json');
    // (Hemos eliminado el bloque para 'tabla-becados-body' de aquí)
    //if ($('#tabla-usuarios-body').length) $.get(basePath + 'UsuarioControlador.php', response => renderizarTablaUsuarios(response.data), 'json');

    // --- FORMULARIOS SIN ARCHIVOS (Categoría y Lugar) ---
    $('#form-categoria, #form-lugar').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        let redirectUrl = form.is('#form-categoria') ? 'categorias.php' : 'lugares.php';
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: r => {
                if (r.status === 'success') {
                    alert(r.message);
                    window.location.href = redirectUrl;
                } else {
                    alert('Error: ' + r.message);
                }
            },
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
            type: 'POST',
            data: new FormData(this),
            dataType: 'json',
            contentType: false,
            processData: false,
            success: r => {
                if (r.status === 'success') {
                    alert(r.message);
                    window.location.href = 'eventos.php';
                } else {
                    alert('Error: ' + r.message);
                }
            },
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
        $(document).on('submit', '.form-eliminar-funcion, #form-agregar-funcion, .form-agregar-ponente, .form-agregar-entrada', function(e) {
            e.preventDefault();
            const form = $(this);
            if (form.is('.form-eliminar-funcion') && !confirm('¿Seguro que quieres eliminar?')) return;
            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                dataType: 'json',
                success: r => {
                    if (r.status === 'success') {
                        location.reload();
                    } else {
                        alert('Error: ' + r.message);
                    }
                },
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
        const exportBtn = $('#export-excel-btn');

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
                        renderizarPaginacionParticipantes(response.paginacion);
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

            if (idEventoSeleccionado) {
                const urlReporte = `../../controller/ReporteControlador.php?id_evento=${idEventoSeleccionado}`;
                exportBtn.attr('href', urlReporte).show();
            } else {
                exportBtn.hide();
            }

            cargarParticipantes();
        });

        $('#search-participante').on('keyup', function() {
            busquedaActual = $(this).val();
            cargarParticipantes();
        });

        $('#pagination-container').on('click', 'a.page-link', function(e) {
            e.preventDefault();
            const pagina = $(this).data('page');
            if (pagina && !$(this).parent().hasClass('disabled')) {
                cargarParticipantes(pagina);
            }
        });
    }

    // --- ¡BLOQUE ÚNICO Y CORREGIDO PARA CAMBIAR ESTADO! ---
    $('#tabla-becados-body').on('click', '.btn-cambiar-estado-becado', function() {
        // Se extraen los datos del botón en el que se hizo clic
        const id = $(this).data('id');
        const estado = $(this).data('estado');
        
        // Se muestra un mensaje de confirmación
        if (confirm(`¿Seguro que quieres cambiar el estado de este becado?`)) {
            
            // Se realiza la llamada al controlador con los parámetros correctos
            $.post(basePath + 'BecadoControlador.php', { 
                accion: 'cambiar_estado', 
                id: id, 
                estado: estado 
            }, response => {
                // Si la respuesta del servidor es exitosa...
                if (response.status === 'success') {
                    // Se recarga la tabla para mostrar el cambio,
                    // manteniendo la búsqueda y paginación actual.
                    const searchTerm = $('#search-becados-input').val(); // <-- Usa el ID correcto
                    const currentPage = $('#pagination-controls .active a').data('page') || 1;
                    cargarBecados(searchTerm, currentPage);
                } else {
                    // Si hay un error, se muestra un mensaje
                    alert('Error: ' + response.message);
                }
            }, 'json');
        }
    });


    // =============================================================
    // LÓGICA PARA EL MÓDULO DE ESCANEO QR (check-in)
    // =============================================================
    if ($('#qr-reader').length) {
        let html5QrCode = null;
        let idEventoSeleccionado = null;
        let busquedaAsistencia = '';

        const resultContainer = $('#scan-result-container');
        const searchInput = $('#search-asistencia');
        const startBtn = $('#start-scan-btn');
        const stopBtn = $('#stop-scan-btn');
        const scannerContainer = $('#scanner-container');
        const participantesContainer = $('#participantes-checkin-container');

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

        function renderizarPaginacionAsistencia(paginacion) {
            const container = $('#pagination-asistencia-container');
            container.empty();
            if (!paginacion || paginacion.total_paginas <= 1) return;

            let html = '<nav><ul class="pagination">';
            const { pagina: current_page, total_paginas } = paginacion;

            // --- Lógica de paginación inteligente ---
            const pagesToShow = [];
            const window = 2;

            pagesToShow.push(1);
            if (current_page > window + 2) pagesToShow.push('...');
            for (let i = Math.max(2, current_page - window); i <= Math.min(total_paginas - 1, current_page + window); i++) pagesToShow.push(i);
            if (current_page < total_paginas - window - 1) pagesToShow.push('...');
            if (total_paginas > 1) pagesToShow.push(total_paginas);

            const uniquePages = [...new Set(pagesToShow)];
            uniquePages.forEach(page => {
                if (page === '...') {
                    html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
                } else {
                    html += `<li class="page-item ${page === current_page ? 'active' : ''}"><a class="page-link" href="#" data-page="${page}">${page}</a></li>`;
                }
            });

            html += '</ul></nav>';
            container.html(html);
        }

        function cargarAsistencia(pagina = 1) {
            if (!idEventoSeleccionado) return;
            participantesContainer.show();
            $('#info-asistencia-container').show(); 
            $('#tabla-asistencia-body').html('<tr><td colspan="4" class="text-center">Cargando...</td></tr>');

            $.get('../../controller/CheckinControlador.php', { id_evento: idEventoSeleccionado, busqueda: busquedaAsistencia, pagina: pagina }, function(response) {
                if (response.status === 'success') {
                    renderizarTablaAsistencia(response.data);
                    renderizarPaginacionAsistencia(response.paginacion);
                    if (response.estadisticas) {
                        $('#total-inscritos').text(response.estadisticas.total_inscritos);
                        $('#total-registrados').text(response.estadisticas.total_registrados);
                        $('#total-faltantes').text(response.estadisticas.total_faltantes);
                    }
                }
            }, 'json');
        }

        $('#evento-checkin-select').on('change', function() {
            idEventoSeleccionado = $(this).val();

            if (idEventoSeleccionado) {
                searchInput.prop('disabled', false);
                startBtn.prop('disabled', false);
                busquedaAsistencia = '';
                searchInput.val('');
                cargarAsistencia();
            } else {
                // --- AÑADIR ESTE ELSE PARA OCULTAR TODO SI NO HAY EVENTO ---
                searchInput.prop('disabled', true);
                startBtn.prop('disabled', true);
                participantesContainer.hide();
                $('#info-asistencia-container').hide();
            }
        });

        searchInput.on('keyup', function() {
            busquedaAsistencia = $(this).val();
            cargarAsistencia();
        });

        $('#pagination-asistencia-container').on('click', 'a.page-link', function(e) {
            e.preventDefault();
            const pagina = $(this).data('page');
            if (pagina && !$(this).parent().hasClass('disabled')) {
                cargarAsistencia(pagina);
            }
        });

        startBtn.on('click', function() {
            scannerContainer.slideDown();
            html5QrCode = new Html5Qrcode("qr-reader");
            $(this).hide();
            stopBtn.show();
            html5QrCode.start({ facingMode: "environment" }, { fps: 10, qrbox: { width: 250, height: 250 } }, onScanSuccess, () => {});
        });

        stopBtn.on('click', function() {
            if (html5QrCode) {
                html5QrCode.stop().then(() => {
                    scannerContainer.slideUp();
                    $(this).hide();
                    startBtn.show();
                });
            }
        });

        function onScanSuccess(decodedText, decodedResult) {
            html5QrCode.pause();
            verificarAsistencia(decodedText, idEventoSeleccionado, true);
        }

        $('#tabla-asistencia-body').on('click', '.btn-marcar-asistencia', function() {
            const idParticipante = $(this).data('id-participante');
            verificarAsistencia(idParticipante, idEventoSeleccionado, false);
        });

        function verificarAsistencia(valor, idEvento, esCedula) {
            const postData = { id_evento: idEvento };
            if (esCedula) {
                postData.cedula = valor;
            } else {
                postData.id_participante = valor;
            }

            $.post('../../controller/CheckinControlador.php', postData, function(response) {
                const alertClass = response.status === 'success' ? 'alert-success' : 'alert-danger';
                let content = `<h5>${escapeHTML(response.message)}</h5>`;
                if (response.participante) content += `<p class="mb-0">${escapeHTML(response.participante)}</p>`;
                resultContainer.html(`<div class="alert ${alertClass}">${content}</div>`);

                if (response.status === 'success') {
                    cargarAsistencia();
                }

                if (esCedula) {
                    setTimeout(() => { if (html5QrCode) html5QrCode.resume(); }, 2000);
                }
            }, 'json');
        }
    }

    // --- LÓGICA PARA EL GRÁFICO Y FILTROS DEL DASHBOARD ---
    if ($('#participantesPorEventoChart').length) {
        // Variable para mantener la instancia del gráfico y poder destruirla
        let myBarChart = null;
        let tipoChart = null;
        let carreraChart = null;
        let nivelChart = null; 

        // --- FUNCIÓN PARA ACTUALIZAR LAS TARJETAS DE DATOS ---
        function actualizarTarjetas(datos) {
            $('#total-eventos').text(datos.total_eventos);
            $('#total-participantes').text(datos.total_participantes);
            $('#total-asistentes').text(datos.total_asistentes);
            
            // LÍNEA AÑADIDA PARA CORREGIR EL ERROR
            $('#total-faltantes').text(datos.total_faltantes); 

            // Formatear como moneda
            $('#total-ingresos').text('$' + parseFloat(datos.total_ingresos).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        }

        // --- FUNCIÓN PARA ACTUALIZAR EL GRÁFICO DE BARRAS ---
        function actualizarGrafico(datosGrafico) {
            // Si ya existe un gráfico, lo destruimos antes de crear uno nuevo para evitar conflictos
            if (myBarChart) {
                myBarChart.destroy();
            }
            
            const ctx = document.getElementById('participantesPorEventoChart').getContext('2d');
            myBarChart = new Chart(ctx, {
                type: 'bar',
                data: datosGrafico,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                // Asegurar que solo se muestren números enteros en el eje Y
                                stepSize: 1,
                                callback: function(value) {
                                    if (Math.floor(value) === value) {
                                        return value;
                                    }
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    }
                }
            });
        }

        // --- Función para generar colores dinámicos ---
        function generarColoresDinamicos(cantidad) {
            const coloresBase = [
                'rgba(54, 162, 235, 0.7)', 'rgba(75, 192, 192, 0.7)', 'rgba(255, 206, 86, 0.7)',
                'rgba(255, 99, 132, 0.7)', 'rgba(153, 102, 255, 0.7)', 'rgba(255, 159, 64, 0.7)',
                'rgba(199, 199, 199, 0.7)', 'rgba(83, 102, 255, 0.7)', 'rgba(10, 207, 151, 0.7)',
                'rgba(255, 10, 10, 0.7)'
            ];
            let colores = [];
            for (let i = 0; i < cantidad; i++) {
                colores.push(coloresBase[i % coloresBase.length]);
            }
            return colores;
        }


        // --- Gráfico para TIPO DE ASISTENTE (Doughnut) ---
        function actualizarGraficoTipo(datos) {
            if (tipoChart) tipoChart.destroy();
            const ctx = document.getElementById('tipoAsistenteChart').getContext('2d');
            datos.datasets[0].backgroundColor = generarColoresDinamicos(datos.labels.length);
            
            tipoChart = new Chart(ctx, {
                type: 'doughnut',
                data: datos,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'top' } }
                }
            });
        }

        // --- Gráfico para CARRERA/CURSO (Barra Horizontal) ---
        function actualizarGraficoCarrera(datos) {
            if (carreraChart) carreraChart.destroy();
            const ctx = document.getElementById('carreraChart').getContext('2d');
            datos.datasets[0].backgroundColor = generarColoresDinamicos(datos.labels.length);

            carreraChart = new Chart(ctx, {
                type: 'bar',
                data: datos,
                options: {
                    indexAxis: 'y', // <-- Esto lo hace horizontal
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } },
                    plugins: { legend: { display: false } }
                }
            });
        }

        // --- Gráfico para NIVEL (Barra Vertical) ---
        function actualizarGraficoNivel(datos) {
            if (nivelChart) nivelChart.destroy();
            const ctx = document.getElementById('nivelChart').getContext('2d');
            datos.datasets[0].backgroundColor = generarColoresDinamicos(datos.labels.length);

            nivelChart = new Chart(ctx, {
                type: 'bar',
                data: datos,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
                    plugins: { legend: { display: false } }
                }
            });
        }


        // --- FUNCIÓN PARA CARGAR TODOS LOS DATOS DEL DASHBOARD (TARJETAS Y GRÁFICO) ---
        function cargarDatosDashboard(eventoId = null) {
            $.ajax({
                url: '../../controller/DashboardControlador.php',
                type: 'GET',
                data: {
                    accion: 'get_datos_dashboard', // Acción unificada en el controlador
                    id_evento: eventoId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        actualizarTarjetas(response.datos_tarjetas);
                        actualizarGrafico(response.datos_grafico);
                        actualizarGraficoTipo(response.datos_tipo_asistente);
                        actualizarGraficoCarrera(response.datos_carrera);
                        actualizarGraficoNivel(response.datos_nivel);

                        // Actualizar el título del gráfico dinámicamente
                        const titulo = eventoId ? `Distribución del Evento Seleccionado` : 'Distribución General por Evento';
                        $('#grafico-titulo').text(titulo);
                    } else {
                        console.error("Error al cargar datos del dashboard:", response.message);
                    }
                },
                error: function() {
                    console.error("Error de comunicación con el servidor al cargar datos del dashboard.");
                }
            });
        }

        // --- FUNCIÓN PARA POBLAR EL MENÚ DESPLEGABLE DE EVENTOS ---
        function cargarEventosDashboard() {
            const select = $('#evento-dashboard-select');
            $.ajax({
                url: '../../controller/DashboardControlador.php',
                type: 'GET',
                data: { accion: 'get_lista_eventos' },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        // 1. Limpia el select y añade la opción principal
                        select.empty().append('<option value="">-- Ver todos los eventos --</option>');
                        
                        // 2. Rellena con los eventos que vienen de la base de datos
                        response.data.forEach(evento => {
                            select.append(`<option value="${evento.id}">${evento.nombre}</option>`);
                        });
                    } else {
                        select.empty().append('<option value="">Error al cargar eventos</option>');
                        console.error("Error lógico al obtener lista de eventos:", response.message);
                    }
                },
                error: function() {
                    select.empty().append('<option value="">Error de conexión</option>');
                    console.error("No se pudo contactar al servidor para obtener la lista de eventos.");
                }
            });
        }
        
        // --- MANEJADOR DE EVENTOS PARA EL FILTRO (DECLARADO UNA SOLA VEZ) ---
        $('#evento-dashboard-select').on('change', function() {
            const eventoId = $(this).val(); // Obtiene el ID del evento seleccionado
            cargarDatosDashboard(eventoId); // Llama a la función que recarga todo con el nuevo ID
        });

        // --- CARGA INICIAL (SE EJECUTA UNA SOLA VEZ CUANDO LA PÁGINA ESTÁ LISTA) ---
        cargarEventosDashboard(); // Primero, rellena el filtro de eventos
        cargarDatosDashboard();   // Luego, carga los datos iniciales (vista general)
    }

    // --- LÓGICA PARA GESTIÓN DE USUARIOS ---
    if ($('#tabla-usuarios-body').length) {
        $.get('../../controller/UsuarioControlador.php', response => renderizarTablaUsuarios(response.data), 'json');

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

        $('#tabla-usuarios-body').on('click', '.btn-reset-pass', function() {
            const id = $(this).data('id');
            const nombre = $(this).data('nombre');
            $('#reset-id-usuario').val(id);
            $('#nombre-usuario-reset').text(nombre);
        });

        let resetSuccessMessage = '';

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
                        resetSuccessMessage = r.message;
                        form[0].reset();
                        $('#resetPasswordModal').modal('hide');
                    } else {
                        alert('Error: ' + r.message);
                    }
                },
                error: () => alert('Error de comunicación.')
            });
        });

        $('#resetPasswordModal').on('hidden.bs.modal', function() {
            if (resetSuccessMessage) {
                alert(resetSuccessMessage);
                resetSuccessMessage = '';
            }
        });

        $('#tabla-usuarios-body').on('click', '.btn-eliminar-usuario', function() {
            if (!confirm('¿Seguro que quieres eliminar a este usuario?')) return;
            const id = $(this).data('id');
            $.post('../../controller/UsuarioControlador.php', {
                accion: 'eliminar',
                id_usuario: id
            }, r => {
                if (r.status === 'success') {
                    $.get('../../controller/UsuarioControlador.php', response => renderizarTablaUsuarios(response.data), 'json');
                } else {
                    alert('Error: ' + r.message);
                }
            }, 'json');
        });
    }

    // --- MANEJADORES DE EVENTOS 
    if ($('#tabla-becados-body').length) {
        // Carga inicial
        cargarBecados();

        // Búsqueda en tiempo real
        let searchTimeout;
        $('#search-becados-input').on('keyup', function() {
            clearTimeout(searchTimeout);
            const searchTerm = $(this).val();
            searchTimeout = setTimeout(() => {
                cargarBecados(searchTerm, 1);
            }, 300);
        });

        // Clic en los botones de paginación
        $('#pagination-controls').on('click', 'a.page-link', function(e) {
            e.preventDefault();
            if ($(this).parent().hasClass('disabled')) return;
            const page = $(this).data('page');
            const searchTerm = $('#search-becados-input').val();
            cargarBecados(searchTerm, page);
        });


        // Manejador para el formulario de importación
        $('#form-importar-becados').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const submitButton = form.find('button[type="submit"]');
            const originalButtonText = submitButton.html();

            submitButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Importando...');

            $.ajax({
                url: basePath + 'BecadoControlador.php',
                type: 'POST',
                data: new FormData(this),
                dataType: 'json',
                contentType: false,
                processData: false,
                success: function(response) {
                    // Si la importación fue exitosa...
                    if (response.status === 'success') {
                        $('#importarBecadosModal').modal('hide'); // Ocultamos el modal
                        
                        // Mostramos la notificación elegante en lugar del alert()
                        showNotification(response.message);

                        // Usamos los datos que ya vinieron en la respuesta para actualizar la tabla
                        // ¡Esto evita la segunda llamada innecesaria al servidor!
                        renderizarTablaBecados(response.data);
                        renderizarPaginacionBecados(response.pagination);
                    } else {
                        // Si hubo un error, también lo mostramos con la nueva notificación
                        showNotification(response.message, 'danger');
                    }
                },
                error: function() {
                    showNotification('Ocurrió un error de comunicación.', 'danger');
                },
                complete: function() {
                    submitButton.prop('disabled', false).html(originalButtonText);
                    form[0].reset();
                }
            });
        });

    }

});
