/**
 * Sistema de formateo automático para números, precios, costos y porcentajes
 * Se aplica automáticamente según la configuración del business
 */

window.BusinessNumberFormatter = (function() {
    'use strict';

    // Configuración por defecto
    let config = {
        decimalSeparator: '.',
        thousandSeparator: ',',
        currencySymbol: '$',
        currencyCode: 'USD'
    };

    // Elementos que se formatean automáticamente
    const selectors = {
        price: '.format-price, .formatted-price, [data-format="price"]',
        cost: '.format-cost, .formatted-cost, [data-format="cost"]',
        number: '.format-number, .formatted-number, [data-format="number"]',
        percentage: '.format-percentage, .formatted-percentage, [data-format="percentage"]'
    };

    /**
     * Actualiza la configuración
     */
    function updateConfig(newConfig) {
        config = Object.assign(config, newConfig);
    }

    /**
     * Formatea un número según la configuración
     */
    function formatNumber(value, decimals = 2) {
        if (!value && value !== 0) return '';
        
        let number = typeof value === 'number' ? value : parseFloat(value);
        if (isNaN(number)) return '';

        let parts = number.toFixed(decimals).split('.');
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, config.thousandSeparator);
        
        return parts.join(config.decimalSeparator);
    }    /**
     * Formatea un precio
     */
    function formatPrice(value, decimals = 2, showSymbol = true) {
        if (!value && value !== 0) return '';
        
        const formatted = formatNumber(value, decimals);
        return showSymbol ? config.currencySymbol + formatted : formatted;
    }

    /**
     * Formatea un costo
     */
    function formatCost(value, decimals = 2, showSymbol = true) {
        return formatPrice(value, decimals, showSymbol);
    }

    /**
     * Formatea un porcentaje
     */
    function formatPercentage(value, decimals = 2) {
        if (!value && value !== 0) return '';
        
        const formatted = formatNumber(value, decimals);
        return formatted + '%';
    }

    /**
     * Convierte un string formateado de vuelta a número
     */
    function parseNumber(value) {
        if (typeof value === 'number') return value;
        if (!value) return 0;
        
        // Remover símbolo de moneda y espacios
        let cleanValue = value.toString()
            .replace(config.currencySymbol, '')
            .replace('%', '')
            .trim();
        
        // Remover separadores de miles
        cleanValue = cleanValue.replace(new RegExp('\\' + config.thousandSeparator, 'g'), '');
        
        // Convertir separador decimal a punto
        cleanValue = cleanValue.replace(new RegExp('\\' + config.decimalSeparator), '.');

        return parseFloat(cleanValue) || 0;
    }

    /**
     * Aplica formato automático a todos los elementos
     */
    function formatAllElements() {
        // Formatear precios
        document.querySelectorAll(selectors.price).forEach(element => {
            const value = element.getAttribute('data-raw-value') || element.textContent;
            const decimals = parseInt(element.getAttribute('data-decimals')) || 2;
            const showSymbol = element.getAttribute('data-show-symbol') !== 'false';
            
            element.textContent = formatPrice(value, decimals, showSymbol);
        });

        // Formatear costos
        document.querySelectorAll(selectors.cost).forEach(element => {
            const value = element.getAttribute('data-raw-value') || element.textContent;
            const decimals = parseInt(element.getAttribute('data-decimals')) || 2;
            const showSymbol = element.getAttribute('data-show-symbol') !== 'false';
            
            element.textContent = formatCost(value, decimals, showSymbol);
        });

        // Formatear números
        document.querySelectorAll(selectors.number).forEach(element => {
            const value = element.getAttribute('data-raw-value') || element.textContent;
            const decimals = parseInt(element.getAttribute('data-decimals')) || 2;
            
            element.textContent = formatNumber(value, decimals);
        });

        // Formatear porcentajes
        document.querySelectorAll(selectors.percentage).forEach(element => {
            const value = element.getAttribute('data-raw-value') || element.textContent;
            const decimals = parseInt(element.getAttribute('data-decimals')) || 2;
            
            element.textContent = formatPercentage(value, decimals);
        });
    }

    /**
     * Configura inputs para formateo automático
     */
    function setupAutoFormatInputs() {
        // Inputs de precio
        document.querySelectorAll('input[data-format="price"], input.format-price').forEach(setupPriceInput);
        
        // Inputs de costo
        document.querySelectorAll('input[data-format="cost"], input.format-cost').forEach(setupCostInput);
        
        // Inputs de número
        document.querySelectorAll('input[data-format="number"], input.format-number').forEach(setupNumberInput);
        
        // Inputs de porcentaje
        document.querySelectorAll('input[data-format="percentage"], input.format-percentage').forEach(setupPercentageInput);
    }

    /**
     * Configura un input de precio
     */
    function setupPriceInput(input) {
        setupNumericInput(input, 'price');
    }

    /**
     * Configura un input de costo
     */
    function setupCostInput(input) {
        setupNumericInput(input, 'cost');
    }

    /**
     * Configura un input de número
     */
    function setupNumberInput(input) {
        setupNumericInput(input, 'number');
    }

    /**
     * Configura un input de porcentaje
     */
    function setupPercentageInput(input) {
        setupNumericInput(input, 'percentage');
    }

    /**
     * Configuración general para inputs numéricos
     */
    function setupNumericInput(input, type) {
        // Evitar configurar múltiples veces
        if (input.hasAttribute('data-formatter-setup')) return;
        input.setAttribute('data-formatter-setup', 'true');

        // Eventos
        input.addEventListener('focus', function() {
            // Al hacer focus, mostrar valor sin formato para edición
            const rawValue = this.getAttribute('data-raw-value') || this.value;
            if (rawValue) {
                const numericValue = parseNumber(rawValue);
                this.value = numericValue.toString().replace('.', config.decimalSeparator);
            }
        });

        input.addEventListener('blur', function() {
            // Al perder el focus, formatear y guardar valor raw
            const numericValue = parseNumber(this.value);
            this.setAttribute('data-raw-value', numericValue);
            
            // Formatear según el tipo
            switch (type) {
                case 'price':
                    this.value = formatPrice(numericValue);
                    break;
                case 'cost':
                    this.value = formatCost(numericValue);
                    break;
                case 'percentage':
                    this.value = formatPercentage(numericValue);
                    break;
                default:
                    this.value = formatNumber(numericValue);
            }
        });

        input.addEventListener('keydown', function(e) {
            // Permitir solo teclas numéricas y navegación
            const allowedKeys = ['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Tab', 'Home', 'End', 'Enter'];
            if (allowedKeys.includes(e.key)) return;

            // Permitir separador decimal configurado
            if (e.key === config.decimalSeparator) {
                // Solo permitir un separador decimal
                if (this.value.includes(config.decimalSeparator)) {
                    e.preventDefault();
                }
                return;
            }

            // Solo permitir números
            if (!/^[0-9]$/.test(e.key)) {
                e.preventDefault();
            }
        });

        // Formatear valor inicial si existe
        if (input.value) {
            const numericValue = parseNumber(input.value);
            input.setAttribute('data-raw-value', numericValue);
            
            // Formatear según el tipo
            switch (type) {
                case 'price':
                    input.value = formatPrice(numericValue);
                    break;
                case 'cost':
                    input.value = formatCost(numericValue);
                    break;
                case 'percentage':
                    input.value = formatPercentage(numericValue);
                    break;
                default:
                    input.value = formatNumber(numericValue);
            }
        }
    }

    /**
     * Inicializa el sistema de formateo
     */
    function init(userConfig) {
        if (userConfig) {
            updateConfig(userConfig);
        }

        // Formatear elementos existentes
        formatAllElements();
        
        // Configurar inputs
        setupAutoFormatInputs();
        
        // Observer para elementos que se agreguen dinámicamente
        if (window.MutationObserver) {
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'childList') {
                        mutation.addedNodes.forEach(function(node) {
                            if (node.nodeType === 1) { // Element node
                                // Formatear elementos display
                                formatAllElements();
                                // Configurar nuevos inputs
                                setupAutoFormatInputs();
                            }
                        });
                    }
                });
            });

            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        }
    }

    // API pública
    return {
        init: init,
        updateConfig: updateConfig,
        formatNumber: formatNumber,
        formatPrice: formatPrice,
        formatCost: formatCost,
        formatPercentage: formatPercentage,
        parseNumber: parseNumber,
        formatAllElements: formatAllElements,
        setupAutoFormatInputs: setupAutoFormatInputs
    };
})();

// Auto-inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Buscar configuración en window.userFormatConfig (compatibilidad con código existente)
    const userConfig = window.userFormatConfig || window.businessFormatConfig;
    
    if (userConfig) {
        window.BusinessNumberFormatter.init(userConfig);
    } else {
        // Inicializar con configuración por defecto
        window.BusinessNumberFormatter.init();
    }
});

// Exportar también las funciones individuales para compatibilidad
window.formatUserNumber = window.BusinessNumberFormatter.formatNumber;
window.parseUserNumber = window.BusinessNumberFormatter.parseNumber;
