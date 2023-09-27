<?php

namespace common\models;

use rico\yii2images\behaviors\ImageBehave;
use Yii;
use yii\db\Query;
use yii\web\UploadedFile;

/**
 * This is the model class for table "recipe_step".
 *
 * @property int $id
 * @property int $recipe_id
 * @property int $number
 * @property string $activity
 * @property string|null $time
 * @property string|null $indicator
 *
 * @property StandardRecipe $recipe
 * @property string $type [varchar(255)]
 */
class RecipeStep extends \yii\db\ActiveRecord
{
    const STEP_TYPE_PROCEDURE = 'procedure';
    const STEP_TYPE_SPECIAL = 'special';

    public $_image = null;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'recipe_step';
    }

    public function behaviors()
    {
        return [
            'image' => [
                'class' => ImageBehave::class,
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['recipe_id', 'activity'], 'required'],
            [['recipe_id', 'number'], 'integer'],
            [['time'], 'safe'],
            [['activity', 'indicator', 'type'], 'string', 'max' => 255],
            [['recipe_id'], 'exist', 'skipOnError' => true, 'targetClass' => StandardRecipe::className(), 'targetAttribute' => ['recipe_id' => 'id']],
            [[
                'activity',
                'indicator'
            ], 'filter', 'filter' => 'trim'],
            [['type'], 'in', 'range' => [self::STEP_TYPE_PROCEDURE, self::STEP_TYPE_SPECIAL]],
            [['_image'], 'image']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'recipe_id' => Yii::t('app', 'Recipe ID'),
            'number' => Yii::t('app', 'Number'),
            'activity' => Yii::t('app', 'Activity'),
            'time' => Yii::t('app', 'Time'),
            'indicator' => Yii::t('app', 'Indicator'),
            'image' => Yii::t('app', "Image")
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $this->computeNumber();
        return true;
    }

    public function afterSave($insert, $changedAttributes)
    {

        $this->uploadImage();

        parent::afterSave($insert, $changedAttributes);
    }

    private function uploadImage()
    {
        $file = UploadedFile::getInstance($this, '_image');
        if($file){
            $this->removeImages();
            $this->attachImage($file->tempName, true);
        }
    }

    public function afterDelete()
    {
        parent::afterDelete();
        $this->fixNumbers();
        $this->removeImages();
    }

    /**
     * Gets query for [[Recipe]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRecipe()
    {
        return $this->hasOne(StandardRecipe::className(), ['id' => 'recipe_id']);
    }

    public function computeNumber()
    {
        $lastStep = RecipeStep::find()
            ->where([
                'recipe_id' => $this->recipe_id,
                'type' => $this->type
            ])
            ->orderBy(['number' => SORT_DESC])
            ->one();

        $this->number = empty($lastStep) ? 1 : $lastStep->number + 1;
    }

    public function fixNumbers()
    {
        $steps = (new Query())
            ->select(["id", 'number'])
            ->from("recipe_step")
            ->where([
                'recipe_step.recipe_id' => $this->recipe_id,
                'recipe_step.type' => $this->type
            ])
            ->orderBy(['recipe_step.number' => SORT_ASC])
            ->all();


        foreach ($steps as $index => $step) {
            Yii::$app->db->createCommand()
                ->update('recipe_step', ['number' => $index + 1], ['id' => $step['id']])
                ->execute();
        }

    }
}
