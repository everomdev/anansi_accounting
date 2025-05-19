// Función para parsear números según la configuración del usuario
function parseUserNumber(value) {
    if (typeof value === 'number') return value;
    if (!value) return 0;
    
    // Obtener configuración
    const config = window.userFormatConfig || {
        decimalSeparator: '.',
        thousandSeparator: ','
    };

    // Limpiar el string de todo excepto números y el separador decimal
    let cleanValue = value.toString()
        // Primero remover el separador de miles
        .replace(new RegExp('\\' + config.thousandSeparator, 'g'), '')
        // Luego reemplazar el separador decimal por punto para el parseo
        .replace(new RegExp('\\' + config.decimalSeparator), '.');

    return parseFloat(cleanValue) || 0;
}

// Función para formatear números según las preferencias del usuario
function formatUserNumber(value, decimals = 2) {
    if (!value && value !== 0) return '';
    
    const config = window.userFormatConfig || {
        decimalSeparator: '.',
        thousandSeparator: ',',
        currencySymbol: '$'
    };

    // Asegurar que value es un número
    let number = typeof value === 'number' ? value : parseFloat(value);
    if (isNaN(number)) return '';

    // Formatear el número
    let parts = number.toFixed(decimals).split('.');
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, config.thousandSeparator);
    
    return parts.join(config.decimalSeparator);
}
