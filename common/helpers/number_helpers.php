<?php

use common\helpers\NumberFormatter;
use common\widgets\NumberDisplayWidget;

/**
 * Funciones helper globales para formateo de números en vistas
 * Estas funciones pueden usarse directamente en cualquier vista
 */

if (!function_exists('formatPrice')) {
    /**
     * Formatea un precio
     */
    function formatPrice($value, $decimals = 2, $showSymbol = true) {
        return NumberFormatter::formatPrice($value, $decimals, $showSymbol);
    }
}

if (!function_exists('formatCost')) {
    /**
     * Formatea un costo
     */
    function formatCost($value, $decimals = 2, $showSymbol = true) {
        return NumberFormatter::formatCost($value, $decimals, $showSymbol);
    }
}

if (!function_exists('formatNumber')) {
    /**
     * Formatea un número
     */
    function formatNumber($value, $decimals = 2) {
        return NumberFormatter::formatNumber($value, $decimals);
    }
}

if (!function_exists('formatPercentage')) {
    /**
     * Formatea un porcentaje
     */
    function formatPercentage($value, $decimals = 2) {
        return NumberFormatter::formatPercentage($value, $decimals);
    }
}

if (!function_exists('parseBusinessNumber')) {
    /**
     * Convierte un número formateado de vuelta a valor numérico
     */
    function parseBusinessNumber($value) {
        return NumberFormatter::parseNumber($value);
    }
}

if (!function_exists('displayPrice')) {
    /**
     * Widget helper para mostrar un precio formateado
     */
    function displayPrice($value, $options = []) {
        return NumberDisplayWidget::widget(array_merge([
            'value' => $value,
            'type' => 'price'
        ], $options));
    }
}

if (!function_exists('displayCost')) {
    /**
     * Widget helper para mostrar un costo formateado
     */
    function displayCost($value, $options = []) {
        return NumberDisplayWidget::widget(array_merge([
            'value' => $value,
            'type' => 'cost'
        ], $options));
    }
}

if (!function_exists('displayNumber')) {
    /**
     * Widget helper para mostrar un número formateado
     */
    function displayNumber($value, $options = []) {
        return NumberDisplayWidget::widget(array_merge([
            'value' => $value,
            'type' => 'number'
        ], $options));
    }
}

if (!function_exists('displayPercentage')) {
    /**
     * Widget helper para mostrar un porcentaje formateado
     */
    function displayPercentage($value, $options = []) {
        return NumberDisplayWidget::widget(array_merge([
            'value' => $value,
            'type' => 'percentage'
        ], $options));
    }
}
