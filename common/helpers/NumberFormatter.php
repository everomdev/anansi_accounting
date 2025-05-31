<?php

namespace common\helpers;

use common\models\Business;
use backend\helpers\RedisKeys;
use Yii;

/**
 * Helper para formatear números, precios, costos y porcentajes según la configuración del business
 */
class NumberFormatter
{
    private static $business = null;
    private static $config = null;

    /**
     * Obtiene la configuración de formato del business actual
     */
    private static function getConfig()
    {
        if (self::$config === null) {
            self::$business = self::getBusiness();
            
            if (self::$business) {
                self::$config = [
                    'decimal_separator' => self::$business->decimal_separator ?? '.',
                    'thousands_separator' => self::$business->thousands_separator ?? ',',
                    'currency_code' => self::$business->currency_code ?? 'USD',
                    'currency_symbol' => self::getCurrencySymbol(self::$business->currency_code ?? 'USD'),
                ];
            } else {
                // Configuración por defecto
                self::$config = [
                    'decimal_separator' => '.',
                    'thousands_separator' => ',',
                    'currency_code' => 'USD',
                    'currency_symbol' => '$',
                ];
            }
        }
        
        return self::$config;
    }

    /**
     * Obtiene el business actual
     */
    private static function getBusiness()
    {
        if (self::$business === null) {
            try {
                // Intentar obtener desde Redis si está disponible
                if (class_exists('\backend\helpers\RedisKeys')) {
                    $businessData = RedisKeys::getBusiness();
                    if ($businessData && isset($businessData['id'])) {
                        self::$business = Business::findOne($businessData['id']);
                    }
                }
                
                // Si no hay business desde Redis, intentar desde el usuario autenticado
                if (!self::$business && !Yii::$app->user->isGuest) {
                    $user = Yii::$app->user->identity;
                    if ($user && method_exists($user, 'getBusiness')) {
                        self::$business = $user->getBusiness()->one();
                    }
                }
            } catch (\Exception $e) {
                // Si hay error, usar configuración por defecto
                Yii::warning('Error getting business for NumberFormatter: ' . $e->getMessage());
            }
        }
        
        return self::$business;
    }

    /**
     * Obtiene el símbolo de la moneda
     */
    private static function getCurrencySymbol($currencyCode)
    {
        try {
            if (class_exists('\Symfony\Component\Intl\Currencies')) {
                $symbol = \Symfony\Component\Intl\Currencies::getSymbol(strtoupper($currencyCode));
                return preg_replace('/[a-zA-Z]/', '', $symbol) ?: '$';
            }
        } catch (\Exception $e) {
            // Si hay error, devolver símbolo por defecto
        }
        
        return '$';
    }

    /**
     * Resetea la configuración (útil para testing o cambios de business)
     */
    public static function resetConfig()
    {
        self::$config = null;
        self::$business = null;
    }

    /**
     * Formatea un número según la configuración del business
     */
    public static function formatNumber($value, $decimals = 2)
    {
        if (!is_numeric($value)) {
            return $value;
        }

        $config = self::getConfig();
        
        $formatted = number_format(
            (float)$value,
            $decimals,
            $config['decimal_separator'],
            $config['thousands_separator']
        );
        
        return $formatted;
    }    /**
     * Formatea un precio con símbolo de moneda
     */
    public static function formatPrice($value, $decimals = 2, $showSymbol = true)
    {
        if (!is_numeric($value)) {
            return $value;
        }

        $config = self::getConfig();
        $formatted = self::formatNumber($value, $decimals);
        
        if ($showSymbol) {
            return $config['currency_symbol'] . $formatted;
        }
        
        return $formatted;
    }

    /**
     * Formatea un costo (igual que precio pero semánticamente diferente)
     */
    public static function formatCost($value, $decimals = 2, $showSymbol = true)
    {
        return self::formatPrice($value, $decimals, $showSymbol);
    }

    /**
     * Formatea un porcentaje
     */
    public static function formatPercentage($value, $decimals = 2)
    {
        if (!is_numeric($value)) {
            return $value;
        }

        $formatted = self::formatNumber($value, $decimals);
        return $formatted . '%';
    }

    /**
     * Convierte un string formateado de vuelta a número (para guardar en BD)
     */
    public static function parseNumber($value)
    {
        if (is_numeric($value)) {
            return (float)$value;
        }

        if (!is_string($value)) {
            return 0;
        }

        $config = self::getConfig();
        
        // Remover símbolo de moneda
        $value = str_replace($config['currency_symbol'], '', $value);
        $value = trim($value);
        
        // Remover separadores de miles
        $value = str_replace($config['thousands_separator'], '', $value);
        
        // Convertir separador decimal a punto
        $value = str_replace($config['decimal_separator'], '.', $value);
        
        return (float)$value;
    }

    /**
     * Obtiene la configuración actual (útil para JavaScript)
     */
    public static function getFormatConfig()
    {
        return self::getConfig();
    }

    /**
     * Genera configuración para JavaScript
     */
    public static function getJsConfig()
    {
        $config = self::getConfig();
        return [
            'decimalSeparator' => $config['decimal_separator'],
            'thousandSeparator' => $config['thousands_separator'],
            'currencySymbol' => $config['currency_symbol'],
            'currencyCode' => $config['currency_code'],
        ];
    }
}
