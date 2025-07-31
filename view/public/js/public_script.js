$(document).ready(function() {
    
    // --- VALIDACIÓN EN TIEMPO REAL ---
    const nombresInput = $('#nombres');
    const apellidosInput = $('#apellidos');
    const cedulaInput = $('#cedula');
    const telefonoInput = $('#telefono');

    // Función de validación de cédula (portada de PHP)
    function validarCedula(cedula) {
        if (typeof cedula !== 'string' || cedula.length !== 10 || !/^\d+$/.test(cedula)) return false;
        const provincia = parseInt(cedula.substring(0, 2), 10);
        if (provincia < 1 || provincia > 24) return false;
        const digitoVerificador = parseInt(cedula[9], 10);
        const coeficientes = [2, 1, 2, 1, 2, 1, 2, 1, 2];
        let suma = 0;
        for (let i = 0; i < 9; i++) {
            let producto = parseInt(cedula[i], 10) * coeficientes[i];
            suma += (producto >= 10) ? producto - 9 : producto;
        }
        const resultado = (suma % 10 === 0) ? 0 : 10 - (suma % 10);
        return resultado === digitoVerificador;
    }

    // Nombres y Apellidos
    nombresInput.on('input', function() {
        let valor = $(this).val();
        $(this).val(valor.toUpperCase()); // Convertir a mayúsculas
        if (/\d/.test(valor)) {
            $('#nombres-error').text('Este campo no puede contener números.');
        } else {
            $('#nombres-error').text('');
        }
        if (valor.trim().split(' ').length === 1 && valor.length > 2) {
            $('#nombres-sugerencia').text('Sugerencia: si tiene dos nombres, por favor ingréselos.');
        } else {
            $('#nombres-sugerencia').text('');
        }
    });

    apellidosInput.on('input', function() {
        let valor = $(this).val();
        $(this).val(valor.toUpperCase());
        if (/\d/.test(valor)) {
            $('#apellidos-error').text('Este campo no puede contener números.');
        } else {
            $('#apellidos-error').text('');
        }
        if (valor.trim().split(' ').length === 1 && valor.length > 2) {
            $('#apellidos-sugerencia').text('Sugerencia: ingrese sus dos apellidos si los tiene.');
        } else {
            $('#apellidos-sugerencia').text('');
        }
    });

    // Cédula
    cedulaInput.on('input', function() {
        let valor = $(this).val().replace(/\D/g, ''); // Permitir solo números
        $(this).val(valor);
        if (valor.length === 10) {
            if (!validarCedula(valor)) {
                $('#cedula-error').text('La cédula ingresada no es válida.');
            } else {
                $('#cedula-error').text('');
            }
        } else if (valor.length > 0) {
            $('#cedula-error').text('La cédula debe tener 10 dígitos.');
        } else {
             $('#cedula-error').text('');
        }
    });

    // Teléfono
    telefonoInput.on('input', function() {
        let valor = $(this).val().replace(/\D/g, '');
        $(this).val(valor);
        if (valor.length > 0 && !valor.startsWith('09')) {
            $('#telefono-error').text('El teléfono debe empezar con 09.');
        } else if (valor.length > 0 && valor.length !== 10) {
            $('#telefono-error').text('El teléfono debe tener 10 dígitos.');
        } else {
            $('#telefono-error').text('');
        }
    });


    // --- LÓGICA PARA MOSTRAR/OCULTAR CAMPOS DE PAGO ---
    $('#tipo_entrada_select').on('change', function() {
        const precio = parseFloat($(this).find('option:selected').data('precio'));

        // Seleccionamos los 3 campos de pago
        const campoBanco = $('#campo-banco');
        const campoTransaccion = $('#campo-transaccion');
        const campoComprobante = $('#campo-comprobante');

        if (precio > 0) {
            // Si hay costo, mostramos los campos y los hacemos obligatorios
            campoBanco.show();
            campoBanco.find('select').prop('required', true);
            campoTransaccion.show();
            campoTransaccion.find('input').prop('required', true);
            campoComprobante.show();
            campoComprobante.find('input').prop('required', true);
        } else {
            // Si es gratuito, los ocultamos y quitamos el 'required'
            campoBanco.hide();
            campoBanco.find('select').prop('required', false);
            campoTransaccion.hide();
            campoTransaccion.find('input').prop('required', false);
            campoComprobante.hide();
            campoComprobante.find('input').prop('required', false);
        }
    }).trigger('change'); // Hacemos que se ejecute al cargar la página para establecer el estado inicial


    // --- LÓGICA DE ENVÍO AJAX DEL FORMULARIO ---
    $('#form-registro-participante').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const submitButton = form.find('button[type="submit"]');
        const originalButtonText = submitButton.html();
        submitButton.prop('disabled', true).html(`<span class="spinner-border spinner-border-sm"></span> Enviando...`);
        const formData = new FormData(this);

        $.ajax({
            url: '../../controller/ParticipanteControlador.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            contentType: false,
            processData: false,
            success: function(response) {
                const alertContainer = $('#alert-container');
                if (response.status === 'success') {
                    alertContainer.html(`<div class="alert alert-success">${response.message}</div>`);
                    form[0].reset();
                    // Ocultamos de nuevo los campos de pago tras un registro exitoso
                    $('#campo-banco, #campo-transaccion, #campo-comprobante').hide();
                } else {
                    // Si hay errores, los mostramos en una lista
                    let errorHtml = '<div class="alert alert-danger"><ul>';
                    response.errors.forEach(error => {
                        errorHtml += `<li>${error}</li>`;
                    });
                    errorHtml += '</ul></div>';
                    alertContainer.html(errorHtml);
                }
            },
            error: function() {
                $('#alert-container').html('<div class="alert alert-danger">Ocurrió un error de comunicación con el servidor.</div>');
            },
            complete: function() {
                submitButton.prop('disabled', false).html(originalButtonText);
            }
        });
    });
});