<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "contact".
 *
 * @property int $id
 * @property string $name
 * @property string $whatsapp
 * @property string $correo
 */
class Contact extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'contact';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'whatsapp', 'correo'], 'required'],
            [['name', 'whatsapp', 'correo'], 'string', 'max' => 255],
            [['correo'], 'email'],
            [['whatsapp'], function($attribute){
                $value = $this->whatsapp;

                // Define una expresión regular para validar números de teléfono en formato internacional (por ejemplo, +1234567890)
                $pattern = '/^\+\d{1,15}$/';

                if (!preg_match($pattern, $value)) {
                    $this->addError('whatsapp', 'El número de teléfono no es válido.');
                    return false;
                }

                return true;
            }],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'whatsapp' => Yii::t('app', 'Whatsapp'),
            'correo' => Yii::t('app', 'Correo'),
        ];
    }
}
