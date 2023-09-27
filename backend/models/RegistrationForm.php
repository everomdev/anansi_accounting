<?php

namespace backend\models;

class RegistrationForm extends \Da\User\Form\RegistrationForm
{
    public $name;
    public $businessName;
    public $planId;
    public function rules()
    {
        return array_merge(
            parent::rules(),
            [
                [['name', 'businessName'], 'required'],
                [['name', 'businessName'], 'string'],
                [['planId'], 'integer'],
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
                'planId' => \Yii::t('app', "Plan"),
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
