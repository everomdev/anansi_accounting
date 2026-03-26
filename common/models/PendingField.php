<?php
namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "pending_field".
 *
 * @property int $id
 * @property string $model_type
 * @property int $model_id
 * @property string $field
 * @property int $created_at
 * @property int $updated_at
 */
class PendingField extends ActiveRecord
{
    public static function tableName()
    {
        return 'pending_field';
    }

    public function rules()
    {
        return [
            [['model_type', 'model_id', 'field'], 'required'],
            [['model_id', 'created_at', 'updated_at'], 'integer'],
            [['model_type'], 'string', 'max' => 50],
            [['field'], 'string', 'max' => 100],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'model_type' => 'Tipo',
            'model_id' => 'ID del registro',
            'field' => 'Campo pendiente',
            'created_at' => 'Creado',
            'updated_at' => 'Actualizado',
        ];
    }
}
