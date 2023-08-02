<?php

namespace backend\models;

class RegistrationForm extends \Da\User\Form\RegistrationForm
{
    public $name;
    public $businessName;

    public function rules()
    {
        return array_merge(
            parent::rules(),
            [
                [['name', 'businessName'], 'required'],
                [['name', 'businessName'], 'string'],
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
