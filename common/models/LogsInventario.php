<?php
namespace common\models;

use Yii;

/**
 * This is the model class for table "logs_inventario".
 *
 * @property int $id
 * @property int $user_id Usuario que ejecutó el ajuste
 * @property int $ingredient_stock_id ID del insumo ajustado
 * @property int $business_id ID del negocio
 * @property string $fecha_ajuste Fecha y hora del ajuste
 * @property float $existencia_anterior Existencia anterior
 * @property float $existencia_nueva Existencia nueva (ajustada)
 * @property string $motivo Motivo del ajuste (opcional)
 * @property string $created_at
 * @property string $updated_at
 *
 * @property User $user
 * @property IngredientStock $ingredientStock
 * @property Business $business
 */
class LogsInventario extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'logs_inventario';
    }

    public function rules()
    {
        return [
            [['user_id', 'ingredient_stock_id', 'business_id', 'fecha_ajuste'], 'required'],
            [['user_id', 'ingredient_stock_id', 'business_id'], 'integer'],
            [['fecha_ajuste', 'created_at', 'updated_at'], 'safe'],
            [['existencia_anterior', 'existencia_nueva'], 'number'],
            [['motivo'], 'string'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['ingredient_stock_id'], 'exist', 'skipOnError' => true, 'targetClass' => IngredientStock::class, 'targetAttribute' => ['ingredient_stock_id' => 'id']],
            [['business_id'], 'exist', 'skipOnError' => true, 'targetClass' => Business::class, 'targetAttribute' => ['business_id' => 'id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'Usuario',
            'ingredient_stock_id' => 'Insumo',
            'business_id' => 'Negocio',
            'fecha_ajuste' => 'Fecha de Ajuste',
            'existencia_anterior' => 'Existencia Anterior',
            'existencia_nueva' => 'Existencia Nueva',
            'motivo' => 'Motivo',
            'created_at' => 'Creado el',
            'updated_at' => 'Actualizado el',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
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
            if ($insert && empty($this->fecha_ajuste)) {
                $this->fecha_ajuste = date('Y-m-d H:i:s');
            }
            
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
            
            if (empty($this->user_id) && !Yii::$app->user->isGuest) {
                $this->user_id = Yii::$app->user->id;
            }
            
            return true;
        }
        return false;
    }

    /**
     * Método estático para crear un log de ajuste de inventario
     */
    public static function crearLog($ingredientStockId, $existenciaAnterior, $existenciaNueva, $motivo = null)
    {
        $log = new self();
        $log->ingredient_stock_id = $ingredientStockId;
        $log->existencia_anterior = $existenciaAnterior;
        $log->existencia_nueva = $existenciaNueva;
        $log->motivo = $motivo;
        
        return $log->save();
    }
}
