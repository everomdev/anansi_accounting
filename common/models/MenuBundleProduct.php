<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "menu_bundle_product".
 *
 * @property int $id
 * @property int|null $entity_id
 * @property string|null $entity_type
 * @property int $bundle_id
 */
class MenuBundleProduct extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'menu_bundle_product';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['bundle_id'], 'required'],
            [['entity_id', 'bundle_id'], 'integer'],
            [['entity_type'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'entity_id' => Yii::t('app', 'Entity ID'),
            'entity_type' => Yii::t('app', 'Entity Type'),
        ];
    }
}
