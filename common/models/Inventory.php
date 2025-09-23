<?php
namespace common\models;

use Yii;

/**
 * This is the model class for table "inventory".
 *
 * @property int $id
 * @property int $ingredient_stock_id
 * @property int $business_id
 * @property float $inventario_almacen
 * @property float $inventario_cocina
 * @property float $inventario_barra
 * @property float $inventario_servicio
 * @property float $inventario_otro
 * @property string $fecha
 *
 * @property IngredientStock $ingredientStock
 * @property Business $business
 */
class Inventory extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'inventory';
    }

    public function rules()
    {
        return [
            [['ingredient_stock_id', 'business_id'], 'required'],
            [['ingredient_stock_id', 'business_id'], 'integer'],
            [['inventario_almacen', 'inventario_cocina', 'inventario_barra', 'inventario_servicio', 'inventario_otro'], 'number'],
            [['fecha'], 'safe'],
            [['ingredient_stock_id'], 'exist', 'skipOnError' => true, 'targetClass' => IngredientStock::class, 'targetAttribute' => ['ingredient_stock_id' => 'id']],
            [['business_id'], 'exist', 'skipOnError' => true, 'targetClass' => Business::class, 'targetAttribute' => ['business_id' => 'id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ingredient_stock_id' => 'Insumo',
            'business_id' => 'Negocio',
            'inventario_almacen' => 'Inventario en almacén',
            'inventario_cocina' => 'Inventario en cocina',
            'inventario_barra' => 'Inventario en barra',
            'inventario_servicio' => 'Inventario en servicio',
            'inventario_otro' => 'Inventario en otro',
            'fecha' => 'Fecha de inventario',
        ];
    }

    public function getIngredientStock()
    {
        return $this->hasOne(IngredientStock::class, ['id' => 'ingredient_stock_id']);
    }

    public function getBusiness()
    {
        return $this->hasOne(Business::class, ['id' => 'business_id']);
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if (empty($this->business_id)) {
                $user = \Yii::$app->user->identity;
                if ($user && isset($user->business_id)) {
                    $this->business_id = $user->business_id;
                } else {
                    // Si el usuario no tiene business_id, intenta obtenerlo de Redis
                    $businessData = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);
                    if ($businessData && isset($businessData['id'])) {
                        $this->business_id = $businessData['id'];
                    }
                }
            }
            return true;
        }
        return false;
    }
}
