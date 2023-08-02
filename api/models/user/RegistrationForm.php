<?php


namespace api\models\user;


use Yii;
use yii\helpers\Html;

class RegistrationForm extends \Da\User\Form\RegistrationForm
{
    public $business_name;
    public $name;

    public function formName()
    {
        return '';
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['business_name', 'name'], 'string']
        ]);
    }
}
