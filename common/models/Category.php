<?php

namespace common\models;

use backend\helpers\RedisKeys;
use Yii;

/**
 * This is the model class for table "category".
 *
 * @property int $id
 * @property string $name
 * @property int|null $builtin
 * @property int $business_id [int]
 * @property int $group_id [int]
 * @property-read mixed $group
 * @property string $key_prefix [varchar(255)]
 */
class Category extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'category';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['builtin', 'business_id', 'group_id'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['business_id'], 'exist', 'targetClass' => Business::class, 'targetAttribute' => ['business_id' => 'id']],
            [['group_id'], 'exist', 'targetClass' => CategoryGroup::class, 'targetAttribute' => ['group_id' => 'id']],
            [['name'], 'validateName'],
            [['key_prefix'], 'string', 'max' => 4],
            [['key_prefix'], 'unique'],
        ];
    }

    public function validateName($attribute)
    {
        $exists = Category::find()->where(['name' => $this->name, 'group_id' => $this->group_id])->one();

        if ($exists) {
            if ($exists->business_id == null) {
                $this->addError($attribute, Yii::t('app', "{category} already exists", ['category' => $this->name]));
                return false;
            }
            $business = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);
            if ($exists->business_id == $business['id']) {
                $this->addError($attribute, Yii::t('app', "{category} already exists", ['category' => $this->name]));
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'builtin' => Yii::t('app', 'Builtin'),
            'business_id' => Yii::t('app', "Business"),
            'group_id' => Yii::t('app', "Group"),
            'key_prefix' => Yii::t('app', "Key Prefix"),
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if (empty($this->business_id)) {
            $this->builtin = 1;
        }

        return true;
    }

    public function getGroup()
    {
        return $this->hasOne(CategoryGroup::class, ['id' => 'group_id']);
    }

    public static function all()
    {
        $business = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);

        return self::find()
            ->where([
                'or',
                ['business_id' => $business['id']],
                ['business_id' => null],
            ])
            ->all();
    }
}
