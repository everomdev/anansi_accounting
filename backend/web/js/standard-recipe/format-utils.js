// Funciones de utilidad para formateo que utilizan el sistema global BusinessNumberFormatter
// Estas funciones mantienen compatibilidad con código existente

// Función para parsear números según la configuración del usuario
function parseUserNumber(value) {
    // Usar el sistema global de formateo si está disponible
    if (window.BusinessNumberFormatter) {
        return window.BusinessNumberFormatter.parseNumber(value);
    }
    
    // Fallback a configuración local si el global no está disponible
    if (typeof value === 'number') return value;
    if (!value) return 0;
    
    const config = window.userFormatConfig || {
        decimalSeparator: '.',
        thousandSeparator: ','
    };

    let cleanValue = value.toString()
        .replace(new RegExp('\\' + config.thousandSeparator, 'g'), '')
        .replace(new RegExp('\\' + config.decimalSeparator), '.');

    return parseFloat(cleanValue) || 0;
}

// Función para formatear números según las preferencias del usuario
function formatUserNumber(value, decimals = 2) {
    // Usar el sistema global de formateo si está disponible
    if (window.BusinessNumberFormatter) {
        return window.BusinessNumberFormatter.formatNumber(value, decimals);
    }
    
    // Fallback a configuración local si el global no está disponible
    if (!value && value !== 0) return '';
    
    const config = window.userFormatConfig || {
        decimalSeparator: '.',
        thousandSeparator: ',',
        currencySymbol: '$'
    };

    let number = typeof value === 'number' ? value : parseFloat(value);
    if (isNaN(number)) return '';

    let parts = number.toFixed(decimals).split('.');
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, config.thousandSeparator);
    
    return parts.join(config.decimalSeparator);
}
