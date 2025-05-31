<?php

namespace common\widgets;

use common\helpers\NumberFormatter;
use yii\base\Widget;
use yii\helpers\Html;

/**
 * Widget para mostrar valores formateados automáticamente
 */
class NumberDisplayWidget extends Widget
{
    public $value;
    public $type = 'number'; // number, price, cost, percentage
    public $decimals = 2;
    public $showSymbol = true;
    public $htmlOptions = [];
    public $tag = 'span';

    public function run()
    {
        $formattedValue = $this->formatValue();
        
        // Agregar clases CSS para identificación
        $cssClass = 'formatted-' . $this->type;
        if (isset($this->htmlOptions['class'])) {
            $this->htmlOptions['class'] .= ' ' . $cssClass;
        } else {
            $this->htmlOptions['class'] = $cssClass;
        }

        // Agregar data attributes para re-formateo dinámico
        $this->htmlOptions['data-raw-value'] = $this->value;
        $this->htmlOptions['data-format-type'] = $this->type;
        $this->htmlOptions['data-decimals'] = $this->decimals;

        return Html::tag($this->tag, $formattedValue, $this->htmlOptions);
    }

    private function formatValue()
    {
        switch ($this->type) {
            case 'price':
                return NumberFormatter::formatPrice($this->value, $this->decimals, $this->showSymbol);
            case 'cost':
                return NumberFormatter::formatCost($this->value, $this->decimals, $this->showSymbol);
            case 'percentage':
                return NumberFormatter::formatPercentage($this->value, $this->decimals);
            case 'number':
            default:
                return NumberFormatter::formatNumber($this->value, $this->decimals);
        }
    }
}
