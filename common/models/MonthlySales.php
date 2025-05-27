<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;
use common\models\StandardRecipe;
use common\models\Menu;

/**
 * This is the model class for table "monthly_sales".
 *
 * @property int $id
 * @property string $model_type
 * @property int $model_id
 * @property int $month
 * @property int $year
 * @property float $sales
 * @property string $created_at
 * @property string $updated_at
 */
class MonthlySales extends ActiveRecord
{
    const TYPE_RECIPE = 'standard_recipe';
    const TYPE_MENU = 'menu';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%monthly_sales}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['model_type', 'model_id', 'month', 'year'], 'required'],
            [['model_id', 'month', 'year'], 'integer'],
            [['sales'], 'number'],
            [['model_type'], 'string', 'max' => 255],
            [['model_type'], 'in', 'range' => [self::TYPE_RECIPE, self::TYPE_MENU]],
            [['month'], 'in', 'range' => range(1, 12)],
            [['year'], 'in', 'range' => [date('Y') - 5, date('Y') + 100]], // permitir -5/+10 años desde el actual
            [['created_at', 'updated_at'], 'safe'],
            // Asegurar que no haya duplicados
            [['model_type', 'model_id', 'month', 'year'], 'unique', 'targetAttribute' => ['model_type', 'model_id', 'month', 'year']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'value' => new Expression('CURRENT_TIMESTAMP'),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'model_type' => 'Tipo',
            'model_id' => 'ID del modelo',
            'month' => 'Mes',
            'year' => 'Año',
            'sales' => 'Ventas',
            'created_at' => 'Creado',
            'updated_at' => 'Actualizado',
        ];    }    /**
     * Guarda o actualiza las ventas para un modelo específico en un mes y año
     * @param string $modelType El tipo de modelo (standard_recipe o menu)
     * @param int $modelId El ID del modelo
     * @param int $month El mes (1-12)
     * @param int $year El año
     * @param float $sales Las ventas a guardar
     * @return bool Si la operación fue exitosa
     */
    public static function saveSales($modelType, $modelId, $month, $year, $sales)
    {
        // Asegurar que todos los parámetros sean válidos
        $month = (int)$month;
        $year = (int)$year;
        $modelId = (int)$modelId;
        $sales = (float)$sales;
        if ($month < 1 || $month > 12 || $year < 2000 || $year > 2100 || $modelId <= 0) {
            Yii::error("Parámetros inválidos al guardar ventas: tipo=$modelType, id=$modelId, mes=$month, año=$year, ventas=$sales");
            return false;
        }
        
        // Validar que el tipo de modelo sea correcto
        if ($modelType !== self::TYPE_RECIPE && $modelType !== self::TYPE_MENU) {
            Yii::error("Tipo de modelo inválido al guardar ventas: $modelType");
            return false;
        }
        
        // Comprobar que el modelo existe en la base de datos
        if ($modelType === self::TYPE_RECIPE) {
            $modelExists = StandardRecipe::find()->where(['id' => $modelId])->exists();
            if (!$modelExists) {
                Yii::error("No existe la receta con ID: $modelId");
                return false;
            }
        } else { // TYPE_MENU
            $modelExists = Menu::find()->where(['id' => $modelId])->exists();
            if (!$modelExists) {
                Yii::error("No existe el combo/menú con ID: $modelId");
                return false;
            }
        }

        try {
            $model = static::findOne([
                'model_type' => $modelType,
                'model_id' => $modelId,
                'month' => $month,
                'year' => $year,
            ]);

        
            if (!$model) {
                $model = new static([
                    'model_type' => $modelType,
                    'model_id' => $modelId,
                    'month' => $month,
                    'year' => $year,
                ]);
            }
            $model->sales = $sales;
            // $result = $model->save();
            if (!$model->validate()) {
                Yii::error("Error de validación: " . print_r($model->errors, true));
                // Intentar guardar incluso con los errores de validación si es necesario
                $result = $model->save(false);
            } else {
                $result = $model->save();
            }
            
            if (!$result) {
                Yii::error("Error al guardar ventas: " . print_r($model->errors, true));
            }
            
            return $result;
        } catch (\Exception $e) {
            Yii::error("Error al guardar ventas: " . $e->getMessage());
            return false;
        }
    }    /**
     * Obtiene las ventas de un modelo para un mes y año específicos
     */
    public static function getSales($modelType, $modelId, $month, $year)
    {
        $model = static::findOne([
            'model_type' => $modelType,
            'model_id' => $modelId,
            'month' => $month,
            'year' => $year,
        ]);

        return $model ? $model->sales : 0;
    }
    
    /**
     * Obtiene la suma total de ventas de un modelo para un año específico
     * @param string $modelType Tipo de modelo (standard_recipe o menu)
     * @param int $modelId ID del modelo
     * @param int $year Año para filtrar las ventas
     * @param int|null $month Mes específico (opcional)
     * @return float Total de ventas del modelo en el año (o mes específico)
     */
    public static function getTotalSales($modelType, $modelId, $year, $month = null)
    {
        $query = static::find()
            ->where([
                'model_type' => $modelType,
                'model_id' => $modelId,
                'year' => $year,
            ]);
            
        if ($month !== null) {
            $query->andWhere(['month' => $month]);
        }
        
        return $query->sum('sales') ?: 0;
    }
}
