$(document).ready(function() {

    // --- LÓGICA PARA EL CONTADOR REGRESIVO (FLIPDOWN) ---
    // Verificamos si el div del contador existe en la página
    if ($('#flipdown').length) {
        // Verificamos que tengamos un timestamp válido desde PHP
        if (typeof unixTimestamp !== 'undefined' && unixTimestamp > 0) {
            
            // Inicializamos FlipDown con nuestro timestamp
            new FlipDown(unixTimestamp, 'flipdown', {
                theme: "dark", // Mantenemos el tema base para sobreescribir los estilos
                headings: ["Días", "Horas", "Minutos", "Segundos"] // <-- AÑADIDO: Etiquetas en español
            }).start();

        } else {
            $('#flipdown').html('<p class="text-muted">La fecha del evento no está definida.</p>');
        }
    }

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

    // Nombres y Apellidos (VALIDACIÓN MEJORADA CON Ñ Y TILDES)
    function validarTexto(input, errorField) {
        let valor = input.val();
        // Permite letras, tildes, Ñ, y espacios. Reemplaza cualquier otra cosa.
        let valorLimpio = valor.toUpperCase().replace(/[^A-ZÁÉÍÓÚÑ\s]/g, '');
        if (valor !== valorLimpio) {
            input.val(valorLimpio);
        }
    }

    nombresInput.on('input', function() {
        validarTexto($(this), $('#nombres-error'));
        // La lógica de la sugerencia se mantiene igual
        if ($(this).val().trim().split(' ').length === 1 && $(this).val().length > 2) {
            $('#nombres-sugerencia').text('Sugerencia: si tiene dos nombres, por favor ingréselos.');
        } else {
            $('#nombres-sugerencia').text('');
        }
    });

    apellidosInput.on('input', function() {
        validarTexto($(this), $('#apellidos-error'));
        // La lógica de la sugerencia se mantiene igual
        if ($(this).val().trim().split(' ').length === 1 && $(this).val().length > 2) {
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
        
        const campoBanco = $('#campo-banco');
        const campoTransaccion = $('#campo-transaccion');
        const campoComprobante = $('#campo-comprobante');

        if (precio > 0) {
            campoBanco.show();
            campoBanco.find('select').prop('required', true);
            campoTransaccion.show();
            campoTransaccion.find('input').prop('required', true);
            campoComprobante.show();
            campoComprobante.find('input').prop('required', true);
        } else {
            campoBanco.hide();
            campoBanco.find('select').prop('required', false);
            campoTransaccion.hide();
            campoTransaccion.find('input').prop('required', false);
            campoComprobante.hide();
            campoComprobante.find('input').prop('required', false);
        }
    }).trigger('change'); // Ejecutar al cargar para el estado inicial


    // --- LÓGICA DE ENVÍO AJAX CON REDIRECCIÓN A PDF ---
    $('#form-registro-participante').on('submit', function(e) {
        e.preventDefault(); // Prevenimos el envío tradicional
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
                    alertContainer.html(`<div class="alert alert-success">${response.message} Se descargará tu boleto en unos segundos...</div>`);
                    form[0].reset();
                    $('#campo-banco, #campo-transaccion, #campo-comprobante').hide();
                    
                    // Redirigir a la página de generación de PDF después de un par de segundos
                    setTimeout(function() {
                        window.location.href = `../../controller/generar_pdf.php?id_participante=${response.id_participante}`;
                        // Limpiamos el mensaje de éxito después de redirigir
                        alertContainer.html(''); 
                    }, 2500);

                } else {
                    let errorHtml = '<div class="alert alert-danger"><ul>';
                    response.errors.forEach(error => { errorHtml += `<li>${error}</li>`; });
                    errorHtml += '</ul></div>';
                    alertContainer.html(errorHtml);
                }
            },
            error: function() {
                $('#alert-container').html('<div class="alert alert-danger">Ocurrió un error de comunicación con el servidor.</div>');
            },
            complete: function() {
                // Restauramos el botón después de la redirección o el error
                setTimeout(function(){
                    submitButton.prop('disabled', false).html(originalButtonText);
                }, 2500);
            }
        });
    });


    // --- LÓGICA PARA NUEVOS CAMPOS DINÁMICOS ---
    // Definimos las sedes para cada tipo. ¡Puedes agregar o quitar sedes aquí fácilmente!
    const sedes = {
        'Instituto': [
            { value: 'Santo Domingo', text: 'SANTO DOMINGO' },
            { value: 'Quininde', text: 'QUININDÉ' },
            { value: 'Quito', text: 'QUITO' },
            { value: 'Machala', text: 'MACHALA' },
            { value: 'Cayambe', text: 'CAYAMBE' },
            { value: 'Quinche', text: 'QUINCHE' },
            { value: 'Quevedo', text: 'QUEVEDO' },
            { value: 'Cuenca', text: 'CUENCA' },
            { value: 'Ambato', text: 'AMBATO' },
            { value: 'Riobamba', text: 'RIOBAMBA' },
            { value: 'Loja', text: 'LOJA' }
        ],
        'Capacitadora': [
            { value: 'El Carmen', text: 'EL CARMEN' },
            { value: 'Babahoyo', text: 'BABAHOYO' },
            { value: 'Guayaquil', text: 'GUAYAQUIL' },
            { value: 'Guayaquil Norte', text: 'GUAYAQUIL NORTE' },
            { value: 'Manta', text: 'MANTA' },
            { value: 'Quinindé 1', text: 'QUININDÉ 1' },
            { value: 'Quevedo', text: 'QUEVEDO' },
            { value: 'El Empalme', text: 'EL EMPALME' },
            { value: 'Portoviejo', text: 'PORTOVIEJO' },
            { value: 'Machala', text: 'MACHALA' },
            { value: 'Huaquillas', text: 'HUAQUILLAS' },
            { value: 'Santa Elena', text: 'SANTA ELENA' },
            { value: 'Tulcán', text: 'TULCÁN' },
            { value: 'Ibarra', text: 'IBARRA' },
            { value: 'Cayambe', text: 'CAYAMBE' },
            { value: 'Latacunga', text: 'LATACUNGA' },
            { value: 'La Maná', text: 'LA MANÁ' },
            { value: 'Caluma', text: 'CALUMA' },
            { value: 'Cuenca', text: 'CUENCA' },
            { value: 'Azogues', text: 'AZOGUES' },
            { value: 'Calderón', text: 'CALDERÓN' },
            { value: 'Quito Norte', text: 'QUITO NORTE' },
            { value: 'Quito Centro', text: 'QUITO CENTRO' },
            { value: 'Quito Sur', text: 'QUITO SUR' },
            { value: 'Sangolquí', text: 'SANGOLQUÍ' },
            { value: 'Ambato', text: 'AMBATO' },
            { value: 'Riobamba', text: 'RIOBAMBA' },
            { value: 'Guaranda', text: 'GUARANDA' },
            { value: 'Loja', text: 'LOJA' }
        ]
    };

    const tipoAsistenteSelect = $('#tipo_asistente_select');
    const campoSede = $('#campo-sede');
    const sedeSelect = $('#sede_select');
    const camposInstituto = $('#campos-instituto');
    const camposCapacitadora = $('#campos-capacitadora');

    // El evento principal que controla todo el flujo del formulario
    tipoAsistenteSelect.on('change', function() {
        const seleccion = $(this).val();

        // 1. Ocultar y deshabilitar todos los campos dependientes por defecto
        campoSede.slideUp();
        sedeSelect.prop('required', false).empty(); // Vaciar las opciones de sede

        camposInstituto.slideUp();
        camposInstituto.find('select').prop('disabled', true);

        camposCapacitadora.slideUp();
        camposCapacitadora.find('select').prop('disabled', true);
        
        // 2. Llenar sedes y mostrar los campos correctos según la selección
        if (seleccion === 'Instituto' || seleccion === 'Capacitadora') {
            
            // Llenar el dropdown de sedes con las opciones correctas
            sedeSelect.append('<option value="" disabled selected>-- Elige una sede --</option>');
            sedes[seleccion].forEach(sede => {
                sedeSelect.append(`<option value="${sede.value}">${sede.text}</option>`);
            });
            
            // Mostrar el campo de sede y hacerlo requerido
            campoSede.slideDown();
            sedeSelect.prop('required', true);

            // Mostrar el bloque de campos correspondiente (Instituto o Capacitadora)
            if (seleccion === 'Instituto') {
                camposInstituto.slideDown();
                camposInstituto.find('select').prop('disabled', false);
            } else { // Si es Capacitadora
                camposCapacitadora.slideDown();
                camposCapacitadora.find('select').prop('disabled', false);
            }

        }
        // Si es "Externo", no se hace nada y todo permanece oculto.
    });
});