<?php

namespace backend\models;

class RegistrationForm extends \Da\User\Form\RegistrationForm
{
    public $name;
    public $businessName;
    public $planId;
    public $couponCode; // Añadir el atributo couponCode
    public $captcha;

    public function rules()
    {
        return array_merge(
            parent::rules(),
            [
                [['name', 'businessName','captcha'], 'required'],
                [['name', 'businessName'], 'string'],
                [['planId'], 'integer'],
                [['couponCode'], 'string'], // Añadir la regla de validación para couponCode
                ['captcha', 'captcha', 'captchaAction' => '/site/captcha'], // Añadir la regla de validación para captcha
            ]
        );
    }

    public function attributeLabels()
    {
        return array_merge(
            parent::attributeLabels(),
            [
                'name' => \Yii::t('app', "Your name"),
                'businessName' => \Yii::t('app', "Your business name"),
                'planId' => \Yii::t('app', "Select plan"),
                'couponCode' => \Yii::t('app', "Enter coupon code"), // Añadir la etiqueta para couponCode
            ]
        );
    }

    public function load($data, $formName = null)
    {
        if (!parent::load($data, $formName)) {
            return false;
        }

        $this->username = $this->email;

        return true;
    }
}