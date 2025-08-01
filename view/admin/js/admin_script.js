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
        // Creamos un enlace para el comprobante, si existe y no es 'N/A'
        let enlaceComprobante = 'N/A';
        if (p.ruta_comprobante && p.ruta_comprobante !== 'N/A') {
            // La ruta desde el admin es diferente a la pública
            enlaceComprobante = `<a href="../${p.ruta_comprobante}" target="_blank" class="btn btn-outline-info btn-sm">Ver</a>`;
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



// =============================================================
// CÓDIGO PRINCIPAL
// =============================================================
$(document).ready(function() {

    const basePath = '../../controller/'; // Ruta base para las llamadas AJAX desde view/admin/

    // --- CARGA INICIAL DE LISTAS ---
    if ($('#tabla-categorias-body').length) $.get(basePath + 'CategoriaControlador.php', response => renderizarTablaCategorias(response.data), 'json');
    if ($('#tabla-lugares-body').length) $.get(basePath + 'LugarControlador.php', response => renderizarTablaLugares(response.data), 'json');
    if ($('#tabla-eventos-body').length) $.get(basePath + 'EventoControlador.php', response => renderizarTablaEventos(response.data), 'json');

    // --- FORMULARIOS SIN ARCHIVOS ---
    $('#form-categoria, #form-lugar').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        let redirectUrl = form.is('#form-categoria') ? 'index.php' : 'lugares.php';
        $.ajax({
            url: form.attr('action'),
            type: 'POST', data: form.serialize(), dataType: 'json',
            success: r => { if(r.status==='success'){ alert(r.message); window.location.href=redirectUrl; } else { alert('Error: '+r.message); }},
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

    
    // --- LÓGICA PARA EL MÓDULO DE PARTICIPANTES (CORREGIDO) ---
    if ($('#evento-select').length) {
        $('#evento-select').on('change', function() {
            const idEvento = $(this).val();
            const tablaBody = $('#tabla-participantes-body');
            const tituloTabla = $('#titulo-tabla-participantes');

            if (idEvento) {
                tituloTabla.text(`Participantes Registrados en "${$(this).find('option:selected').text()}"`).show();
                tablaBody.html('<tr><td colspan="5" class="text-center">Cargando participantes...</td></tr>');
                
                $.ajax({
                    url: basePath + 'ParticipanteControlador.php', // Usamos la ruta base
                    type: 'GET',
                    data: { id_evento: idEvento },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            renderizarTablaParticipantes(response.data);
                        } else {
                            tablaBody.html('<tr><td colspan="5" class="text-center text-danger">Error al cargar los datos.</td></tr>');
                        }
                    },
                    error: function() {
                        tablaBody.html('<tr><td colspan="5" class="text-center text-danger">Error de comunicación.</td></tr>');
                    }
                });
            }
        });
    }
}); 