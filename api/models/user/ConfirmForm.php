<?php


namespace api\models\user;


use common\models\User;
use Yii;
use yii\base\Model;

class ConfirmForm extends Model
{

    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $code;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'code'], 'required'],
            ['id', 'exist', 'targetClass' => User::class, 'targetAttribute' => ['id' => 'id']],
            ['code', 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'code' => Yii::t('app', 'Code'),
        ];
    }

}
