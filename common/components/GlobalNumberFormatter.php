<?php

namespace common\components;

use common\helpers\NumberFormatter;
use yii\base\Component;
use yii\web\View;

/**
 * Component global para manejo de formateo de números
 */
class GlobalNumberFormatter extends Component
{
    /**
     * Auto-registrar assets JavaScript en todas las vistas
     */
    public $autoRegisterJs = true;

    /**
     * Auto-registrar configuración de formato en JavaScript
     */
    public $autoRegisterConfig = true;

    public function init()
    {
        parent::init();
        
        if ($this->autoRegisterJs || $this->autoRegisterConfig) {
            // Registrar eventos para auto-registro
            \Yii::$app->on(\yii\web\Application::EVENT_BEFORE_ACTION, [$this, 'registerAssets']);
        }
    }

    /**
     * Registra automáticamente los assets necesarios
     */
    public function registerAssets($event)
    {
        if (\Yii::$app instanceof \yii\web\Application) {
            $view = \Yii::$app->getView();
            
            if ($this->autoRegisterJs) {
                $this->registerJavaScript($view);
            }
            
            if ($this->autoRegisterConfig) {
                $this->registerConfiguration($view);
            }
        }
    }

    /**
     * Registra el JavaScript de formateo
     */
    public function registerJavaScript($view)
    {
        $jsFile = '@common/web/js/business-number-formatter.js';
        
        // Verificar si el archivo existe
        if (file_exists(\Yii::getAlias($jsFile))) {
            $view->registerJsFile(
                \Yii::$app->assetManager->getPublishedUrl($jsFile),
                ['position' => View::POS_HEAD]
            );
        }
    }

    /**
     * Registra la configuración de formato en JavaScript
     */
    public function registerConfiguration($view)
    {
        $config = NumberFormatter::getJsConfig();
        
        $view->registerJsVar('businessFormatConfig', $config);
        
        // Mantener compatibilidad con código existente
        $view->registerJsVar('userFormatConfig', $config);
    }

    /**
     * Métodos de acceso directo para usar en vistas
     */
    public static function price($value, $decimals = 2, $showSymbol = true)
    {
        return NumberFormatter::formatPrice($value, $decimals, $showSymbol);
    }

    public static function cost($value, $decimals = 2, $showSymbol = true)
    {
        return NumberFormatter::formatCost($value, $decimals, $showSymbol);
    }

    public static function number($value, $decimals = 2)
    {
        return NumberFormatter::formatNumber($value, $decimals);
    }

    public static function percentage($value, $decimals = 2)
    {
        return NumberFormatter::formatPercentage($value, $decimals);
    }

    public static function parse($value)
    {
        return NumberFormatter::parseNumber($value);
    }
}
