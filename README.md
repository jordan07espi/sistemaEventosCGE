# Sistema de Gestión de Eventos (CGE)

Un sistema web completo para la gestión y publicación de eventos, desarrollado con PHP, MySQL, y jQuery. Permite a los administradores gestionar categorías, lugares, y eventos con sus respectivas funciones, mientras que los usuarios públicos pueden ver los eventos disponibles y registrarse en ellos.

## Características Principales

### Panel de Administración

* **Gestión de Categorías**: Crear, editar y cambiar el estado (activar/desactivar) de las categorías de los eventos.
* **Gestión de Lugares**: Registrar, editar y eliminar los lugares donde se realizarán los eventos, incluyendo detalles como dirección, ciudad y capacidad.
* **Gestión de Eventos**:
    * Crear y editar eventos, asociándolos a una categoría.
    * Subir un banner o imagen principal para cada evento.
    * Visualizar una lista de todos los eventos con su estado y categoría.
* **Detalle del Evento (Admin)**:
    * Añadir múltiples fechas o funciones (calendarios) a un mismo evento.
    * Asignar ponentes a cada función del evento.
    * Definir diferentes tipos de entrada (tickets) para cada función, con su precio, cantidad y detalles.
    * Toda la gestión se realiza de forma dinámica con AJAX, sin necesidad de recargar la página.

### Vista Pública

* **Página Principal**: Muestra una galería con los próximos eventos que se encuentran activos.
* **Página de Registro**:
    * Muestra los detalles completos de un evento, incluyendo su descripción, banner y las funciones programadas.
    * Permite a los usuarios registrarse a través de un formulario.
* **Formulario de Registro de Participantes**:
    * Validaciones para evitar registros duplicados por cédula en el mismo evento o por número de transacción.
    * Campo para subir un comprobante de pago (imagen o PDF).
    * El formulario se procesa con AJAX, mostrando mensajes de éxito o error dinámicamente.

## Tecnologías Utilizadas

* **Backend**: PHP
* **Base de Datos**: MySQL/MariaDB
* **Frontend**: HTML5, CSS3, JavaScript
* **Librerías**:
    * [jQuery](https://jquery.com/) para la manipulación del DOM y peticiones AJAX.
    * [Bootstrap 5](https://getbootstrap.com/) para el diseño y la interfaz de usuario.

## Instalación

1.  **Clonar el repositorio**:
    ```bash
    git clone [https://github.com/tu_usuario/tu_repositorio.git](https://github.com/tu_usuario/tu_repositorio.git)
    ```

2.  **Base de Datos**:
    * Crea una base de datos en tu servidor MySQL (por ejemplo, `sistema_eventos_db`).
    * Importa el archivo `sistema_eventos_db (4).sql` para crear todas las tablas y relaciones necesarias.

3.  **Configuración**:
    * Abre el archivo `sistemaEventos/config/Conexion.php`.
    * Modifica las variables `$host`, `$db_name`, `$username`, y `$password` con los datos de tu servidor de base de datos.

4.  **Servidor Web**:
    * Copia la carpeta del proyecto a tu servidor web (por ejemplo, en `htdocs/` si usas XAMPP).
    * Asegúrate de que el servidor tenga permisos de escritura en las carpetas `sistemaEventos/uploads/banners/` y `sistemaEventos/uploads/comprobantes/`.

5.  **Acceder**:
    * **Vista Pública**: Abre tu navegador y ve a `http://localhost/sistemaEventos/`.
    * **Panel de Administración**: Accede a `http://localhost/sistemaEventos/view/admin/`.

## Contribuciones

Las contribuciones son bienvenidas. Si deseas mejorar este proyecto, por favor, haz un "fork" del repositorio, crea una nueva rama y envía un "pull request" con tus cambios.