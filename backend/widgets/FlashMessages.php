<?php

namespace backend\widgets;

use kartik\growl\Growl;
use Yii;
use yii\bootstrap5\Button;
use yii\bootstrap5\Html;

class FlashMessages extends \yii\bootstrap5\Widget
{

    public function run()
    {
        $messages = [];
        $flashes = Yii::$app->getSession()->getAllFlashes();
        foreach ($flashes as $type => $message){
            $messages[] = Growl::widget([
                'type' => $this->getType($type),
                'body' => $message,
                'showSeparator' => false,
                'delay' => 0,
                'pluginOptions' => [
                    'showProgressbar' => false,
                    'placement' => [
                        'from' => 'top',
                        'align' => 'right',
                    ]
                ],
                'closeButton' => [
                    'class' => 'btn-close',
                    'type' => 'button',
                    'data-bs-dismiss' => 'alert',
                    'content' => ''
                ]
            ]);
        }

        return implode('<br>',$messages);
    }

    private function getType($type){
        switch ($type){
            case 'success': return Growl::TYPE_SUCCESS;
            case 'warning': return Growl::TYPE_WARNING;
            case 'info': return Growl::TYPE_INFO;
            case 'error':
            case 'danger': return Growl::TYPE_DANGER;
            default:
                return Growl::TYPE_CUSTOM;
        }
    }

    private function getTitle($type){
        switch ($type) {
            case 'success':
                return Yii::t('app', 'Success');
            case 'warning':
                return Yii::t('app', 'Warning');
            case 'info':
                return Yii::t('app', 'Information');
            case 'error':
            case 'danger':
            return Yii::t('app', 'Error');
            default:
                return Yii::t('app', 'Information');
        }
    }
}
