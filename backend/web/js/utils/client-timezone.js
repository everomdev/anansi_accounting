/**
 * Utilidades para manejar fechas y zona horaria del cliente
 */

// Detectar y almacenar la zona horaria del cliente
function getClientTimezone() {
    try {
        return Intl.DateTimeFormat().resolvedOptions().timeZone;
    } catch (e) {
        // Fallback a offset si Intl no está disponible
        const offset = new Date().getTimezoneOffset();
        const hours = Math.abs(Math.floor(offset / 60));
        const minutes = Math.abs(offset % 60);
        const sign = offset <= 0 ? '+' : '-';
        return `UTC${sign}${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;
    }
}

// Guardar zona horaria en un campo oculto del formulario
function injectClientTimezone(formId) {
    const form = document.getElementById(formId);
    if (!form) return;
    
    // Buscar si ya existe el campo
    let tzInput = form.querySelector('input[name="client_timezone"]');
    
    if (!tzInput) {
        tzInput = document.createElement('input');
        tzInput.type = 'hidden';
        tzInput.name = 'client_timezone';
        form.appendChild(tzInput);
    }
    
    tzInput.value = getClientTimezone();
}

// Formatear fecha a formato local del cliente
function formatDateToLocal(dateString, includeTime = true) {
    if (!dateString) return '';
    
    try {
        const date = new Date(dateString);
        const timezone = getClientTimezone();
        
        const options = {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            timeZone: timezone
        };
        
        if (includeTime) {
            options.hour = '2-digit';
            options.minute = '2-digit';
            options.hour12 = false;
        }
        
        return new Intl.DateTimeFormat('es-MX', options).format(date);
    } catch (e) {
        return dateString;
    }
}

// Obtener fecha/hora actual del cliente en formato para input datetime-local
function getCurrentDateTimeForInput() {
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    
    return `${year}-${month}-${day} ${hours}:${minutes}`;
}

// Calcular fecha máxima permitida basado en configuración
function calculateMaxRequiredDate(maxDays) {
    const now = new Date();
    const maxDate = new Date(now.getTime() + (maxDays * 24 * 60 * 60 * 1000));
    
    const year = maxDate.getFullYear();
    const month = String(maxDate.getMonth() + 1).padStart(2, '0');
    const day = String(maxDate.getDate()).padStart(2, '0');
    
    return `${year}-${month}-${day}`;
}

// Exportar funciones para uso global
window.ClientTimezone = {
    get: getClientTimezone,
    inject: injectClientTimezone,
    format: formatDateToLocal,
    getCurrentDateTime: getCurrentDateTimeForInput,
    calculateMaxDate: calculateMaxRequiredDate
};
