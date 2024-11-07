<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "menu_bundle".
 *
 * @property int $id
 * @property string|null $date
 * @property int $business_id
 *
 * @property Business $business
 */
class MenuBundle extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'menu_bundle';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['date'], 'safe'],
            [['business_id'], 'required'],
            [['business_id'], 'integer'],
            [['business_id'], 'exist', 'skipOnError' => true, 'targetClass' => Business::className(), 'targetAttribute' => ['business_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'date' => Yii::t('app', 'Date'),
            'business_id' => Yii::t('app', 'Business ID'),
        ];
    }

    /**
     * Gets query for [[Business]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBusiness()
    {
        return $this->hasOne(Business::className(), ['id' => 'business_id']);
    }

    public function getStandardRecipes($returnIds = false)
    {
        $bundleRecipes = MenuBundleProduct::find()
            ->where([
                'bundle_id' => $this->id,
                'entity_type' => StandardRecipe::class
            ])
            ->select(['entity_id'])
            ->column();



        return $returnIds ? $bundleRecipes : StandardRecipe::find()
            ->where(['id' => $bundleRecipes]);
    }

    public function getCombos($returnIds = false)
    {
        $bundleRecipes = MenuBundleProduct::find()
            ->where([
                'bundle_id' => $this->id,
                'entity_type' => Menu::class
            ])
            ->select(['entity_id'])
            ->column();

        return $returnIds ? $bundleRecipes : Menu::find()
            ->where(['id' => $bundleRecipes]);
    }
}
