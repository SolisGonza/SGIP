// Variables globales del sistema
window.SGIP = {
    baseUrl: '/SGIP/',
    debug: true,
    user: null,
    permissions: {}
};



/**
 * Función principal para realizar peticiones AJAX
 * @param {Object} config - Configuración de la petición
 * @param {string} config.url - URL de la petición
 * @param {string} config.data - Datos a enviar (JSON string)
 * @param {boolean} config.parseJson - Si debe parsear la respuesta como JSON
 * @param {Function} config.successCallback - Callback de éxito
 * @param {Function} config.errorCallback - Callback de error
 * @param {string} config.method - Método HTTP (POST por defecto)
 * @param {number} config.timeout - Timeout en milisegundos
 */
function ajaxRequest(config) {
    // Configuración por defecto
    const defaults = {
        method: 'POST',
        parseJson: true,
        timeout: 30000,
        data: null,
        successCallback: null,
        errorCallback: null
    };

    // Merge configuración
    config = Object.assign({}, defaults, config);

    // Validar URL requerida
    if (!config.url) {
        console.error('ajaxRequest: URL es requerida');
        return;
    }

    // Ajustar URL si es relativa
    config.url = config.url.replace('/SGIP/', '/SGIP/');


    // Log de debug
    if (SGIP.debug) {
        console.log('ajaxRequest:', {
            url: config.url,
            method: config.method,
            data: config.data
        });
    }

    // Crear XMLHttpRequest
    const xhr = new XMLHttpRequest();

    // Configurar timeout
    xhr.timeout = config.timeout;

    // Manejadores de eventos
    xhr.onreadystatechange = function () {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                try {
                    let response = xhr.responseText;

                    // Parsear JSON si está configurado
                    if (config.parseJson) {
                        response = JSON.parse(response);
                    }

                    // Log de respuesta en debug
                    if (SGIP.debug) {
                        console.log('ajaxRequest response:', response);
                    }

                    // Ejecutar callback de éxito
                    if (config.successCallback) {
                        config.successCallback(response);
                    }

                } catch (error) {
                    console.error('Error parsing JSON response:', error);
                    console.error('Raw response:', xhr.responseText);

                    if (config.errorCallback) {
                        config.errorCallback({
                            status: 'parse_error',
                            message: 'Error al procesar la respuesta del servidor',
                            error: error,
                            rawResponse: xhr.responseText
                        });
                    } else {
                        showSGIPError('Error de Respuesta', 'Error al procesar la respuesta del servidor');
                    }
                }
            } else {
                // Error HTTP
                const errorData = {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    response: xhr.responseText
                };

                console.error('ajaxRequest HTTP Error:', errorData);

                if (config.errorCallback) {
                    config.errorCallback(errorData);
                } else {
                    let errorMessage = 'Error de conexión con el servidor';

                    switch (xhr.status) {
                        case 404:
                            errorMessage = 'Recurso no encontrado (404)';
                            break;
                        case 500:
                            errorMessage = 'Error interno del servidor (500)';
                            break;
                        case 403:
                            errorMessage = 'Acceso denegado (403)';
                            break;
                        case 0:
                            errorMessage = 'Error de red o servidor no disponible';
                            break;
                    }

                    showSGIPError('Error de Conexión', errorMessage);
                }
            }
        }
    };

    // Manejador de timeout
    xhr.ontimeout = function () {
        console.error('ajaxRequest timeout:', config.url);

        if (config.errorCallback) {
            config.errorCallback({
                status: 'timeout',
                message: 'Tiempo de espera agotado'
            });
        } else {
            showSGIPError('Tiempo Agotado', 'La petición tardó demasiado en responder');
        }
    };

    // Configurar y enviar petición
    xhr.open(config.method, config.url, true);

    // Headers comunes
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

    if (config.method === 'POST' && config.data) {
        xhr.setRequestHeader('Content-Type', 'application/json; charset=UTF-8');
        xhr.send(config.data);
    } else {
        xhr.send();
    }
}

/**
 * Función para mostrar toast notifications
 * @param {string} type - success, error, warning, info
 * @param {string} title - Título del toast
 * @param {string} message - Mensaje del toast
 */
function showToast(type, title, message) {
    // Verificar si SweetAlert2 está disponible
    if (typeof Swal !== 'undefined') {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });

        Toast.fire({
            icon: type,
            title: title,
            text: message
        });
    } else {
        // Fallback a console
        console.log(`[${type.toUpperCase()}] ${title}: ${message}`);
    }
}

/**
 * Función para mostrar errores del sistema
 * @param {string} title - Título del error
 * @param {string} message - Mensaje del error
 */
function showSGIPError(title, message) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'error',
            title: title,
            text: message,
            footer: '<div>Si el problema persiste, comunícate con Soporte Técnico.</div>',
            confirmButtonText: 'Entendido'
        });
    } else {
        alert(`${title}: ${message}`);
    }
}

/**
 * Función para mostrar confirmaciones
 * @param {string} title - Título de confirmación
 * @param {string} message - Mensaje de confirmación
 * @param {Function} callback - Callback a ejecutar si confirma
 * @param {string} confirmText - Texto del botón confirmar
 * @param {string} cancelText - Texto del botón cancelar
 */
function showSGIPConfirm(title, message, callback, confirmText = 'Confirmar', cancelText = 'Cancelar') {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: title,
            text: message,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: confirmText,
            cancelButtonText: cancelText,
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed && callback) {
                callback();
            }
        });
    } else {
        if (confirm(`${title}\n${message}`) && callback) {
            callback();
        }
    }
}

/**
 * Función para mostrar loading
 * @param {string} message - Mensaje de loading
 */
function showSGIPLoading(message = 'Cargando...') {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: message,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    }
}

function getCurrentSGIPUser() {
    return SGIP.user;
}

/**
 * Función para debug del sistema
 * @param {*} data - Datos a mostrar en console
 * @param {string} label - Etiqueta para el debug
 */
function debugSGIP(data, label = 'SGIP Debug') {
    if (SGIP.debug) {
        console.group(label);
        console.log(data);
        console.groupEnd();
    }
}
/**
 * Función para ocultar loading
 */
function hideSGIPLoading() {
    if (typeof Swal !== 'undefined') {
        Swal.close();
    }
}

document.addEventListener('DOMContentLoaded', function () {
    debugSGIP('SgIP Global functions loaded', 'Sistema');

    // Cargar datos del usuario desde las variables PHP si existen
    if (typeof window.currentUser !== 'undefined') {
        SGIP.user = window.currentUser;
    }

    if (typeof window.userPermissions !== 'undefined') {
        SGIP.permissions = window.userPermissions;
    }

    debugSGIP({
        user: SGIP.user,
        permissions: SGIP.permissions,
        baseUrl: SGIP.baseUrl
    }, 'Datos del Sistema');
});

// Exponer funciones globalmente
window.ajaxRequest = ajaxRequest;
window.showToast = showToast;
window.showSGIPError = showSGIPError;
window.showSGIPConfirm = showSGIPConfirm;
window.showSGIPLoading = showSGIPLoading;
window.hideSGIPLoading = hideSGIPLoading;
window.debugSGIP = debugSGIP;
window.getCurrentSGIPUser = getCurrentSGIPUser;

console.log('%cSIP Global JS cargado correctamente', 'color: #28a745; font-weight: bold;');