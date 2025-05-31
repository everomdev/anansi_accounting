<?php

namespace common\behaviors;

use common\helpers\NumberFormatter;
use yii\base\Behavior;
use yii\db\ActiveRecord;

/**
 * Behavior para formatear automáticamente campos numéricos en modelos
 */
class NumberFormatterBehavior extends Behavior
{
    /**
     * Campos que contienen precios
     */
    public $priceFields = [];
    
    /**
     * Campos que contienen costos
     */
    public $costFields = [];
    
    /**
     * Campos que contienen números simples
     */
    public $numberFields = [];
    
    /**
     * Campos que contienen porcentajes
     */
    public $percentageFields = [];
    
    /**
     * Número de decimales por defecto
     */
    public $defaultDecimals = 2;

    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_INSERT => 'beforeSave',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeSave',
            ActiveRecord::EVENT_AFTER_FIND => 'afterFind',
        ];
    }

    /**
     * Convierte valores formateados a números antes de guardar
     */
    public function beforeSave($event)
    {
        $allFields = array_merge(
            $this->priceFields,
            $this->costFields,
            $this->numberFields,
            $this->percentageFields
        );

        foreach ($allFields as $field) {
            if (isset($this->owner->$field) && is_string($this->owner->$field)) {
                $this->owner->$field = NumberFormatter::parseNumber($this->owner->$field);
            }
        }
    }

    /**
     * Opcionalmente formatea valores después de cargar desde BD
     * (Solo si se requiere formateo automático en el modelo)
     */
    public function afterFind($event)
    {
        // Por ahora no formateamos automáticamente después de find
        // para mantener los valores numéricos puros en el modelo
        // El formateo se hará en las vistas
    }

    /**
     * Métodos helper para formatear campos específicos
     */
    public function getFormattedPrice($field, $decimals = null)
    {
        $decimals = $decimals ?? $this->defaultDecimals;
        return NumberFormatter::formatPrice($this->owner->$field, $decimals);
    }

    public function getFormattedCost($field, $decimals = null)
    {
        $decimals = $decimals ?? $this->defaultDecimals;
        return NumberFormatter::formatCost($this->owner->$field, $decimals);
    }

    public function getFormattedNumber($field, $decimals = null)
    {
        $decimals = $decimals ?? $this->defaultDecimals;
        return NumberFormatter::formatNumber($this->owner->$field, $decimals);
    }

    public function getFormattedPercentage($field, $decimals = null)
    {
        $decimals = $decimals ?? $this->defaultDecimals;
        return NumberFormatter::formatPercentage($this->owner->$field, $decimals);
    }
}
