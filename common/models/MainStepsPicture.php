<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "main_steps_pictures".
 *
 * @property int $id
 * @property int $standard_recipe_id
 * @property string|null $description
 *
 * @property StandardRecipe $standardRecipe
 */
class MainStepsPicture extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'main_steps_pictures';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['standard_recipe_id'], 'required'],
            [['standard_recipe_id'], 'integer'],
            [['description'], 'string'],
            [['standard_recipe_id'], 'exist', 'skipOnError' => true, 'targetClass' => StandardRecipe::className(), 'targetAttribute' => ['standard_recipe_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'standard_recipe_id' => 'Standard Recipe ID',
            'description' => 'Description',
        ];
    }

    /**
     * Gets query for [[StandardRecipe]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStandardRecipe()
    {
        return $this->hasOne(StandardRecipe::className(), ['id' => 'standard_recipe_id']);
    }
}
