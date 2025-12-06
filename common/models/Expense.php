<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "expenses".
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int $business_id
 * @property int|null $provider_id
 * @property float $amount
 * @property int|null $unit_measurement_id
 * @property int|null $category_id
 * @property string $frequency
 * @property string $expense_date
 * @property string|null $observations
 * @property string $key
 * @property int|null $is_active
 * @property int|null $is_recurring
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property Business $business
 * @property Provider $provider
 * @property ExpenseUnitMeasurement $unitMeasurement
 * @property ExpenseCategory $category
 */
class Expense extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'expenses';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new \yii\db\Expression('NOW()'),
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'business_id', 'category_id'], 'required'],
            [['business_id', 'provider_id', 'unit_measurement_id', 'category_id', 'is_active', 'is_recurring'], 'integer'],
            [['amount'], 'number', 'min' => 0],
            [['amount', 'frequency', 'expense_date'], 'required', 'when' => function($model) {
                return $model->is_recurring == 1;
            }, 'whenClient' => "function (attribute, value) {
                return $('#expense-is_recurring').is(':checked');
            }"],
            [['expense_date'], 'date', 'format' => 'php:Y-m-d'],
            [['description', 'observations'], 'string'],
            [['name'], 'string', 'max' => 255],
            [['frequency'], 'string', 'max' => 50],
            [['frequency'], 'in', 'range' => ['unico', 'diario', 'semanal', 'quincenal', 'mensual', 'bimestral', 'trimestral', 'semestral', 'anual']],
            [['key'], 'string', 'max' => 255],
            [['key', 'business_id'], 'unique', 'targetAttribute' => ['key', 'business_id']],
            [['business_id'], 'exist', 'skipOnError' => true, 'targetClass' => Business::class, 'targetAttribute' => ['business_id' => 'id']],
            [['provider_id'], 'exist', 'skipOnError' => true, 'targetClass' => Provider::class, 'targetAttribute' => ['provider_id' => 'id']],
            [['unit_measurement_id'], 'exist', 'skipOnError' => true, 'targetClass' => ExpenseUnitMeasurement::class, 'targetAttribute' => ['unit_measurement_id' => 'id']],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => ExpenseCategory::class, 'targetAttribute' => ['category_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Nombre del Gasto',
            'description' => 'Descripción',
            'business_id' => 'Negocio',
            'provider_id' => 'Proveedor',
            'amount' => 'Monto',
            'unit_measurement_id' => 'Unidad de Medida',
            'category_id' => 'Categoría',
            'frequency' => 'Frecuencia',
            'expense_date' => 'Fecha del Gasto',
            'observations' => 'Observaciones',
            'key' => 'Clave',
            'is_active' => 'Activo',
            'is_recurring' => 'Gasto Frecuente',
            'created_at' => 'Fecha de Creación',
            'updated_at' => 'Fecha de Actualización',
        ];
    }

    /**
     * Gets query for [[Business]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBusiness()
    {
        return $this->hasOne(Business::class, ['id' => 'business_id']);
    }

    /**
     * Gets query for [[Provider]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProvider()
    {
        return $this->hasOne(Provider::class, ['id' => 'provider_id']);
    }

    /**
     * Gets query for [[ExpenseMovements]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getExpenseMovements()
    {
        return $this->hasMany(ExpenseMovement::class, ['expense_id' => 'id']);
    }

    /**
     * Gets query for [[UnitMeasurement]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUnitMeasurement()
    {
        return $this->hasOne(ExpenseUnitMeasurement::class, ['id' => 'unit_measurement_id']);
    }

    /**
     * Gets query for [[Category]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(ExpenseCategory::class, ['id' => 'category_id']);
    }

    /**
     * Opciones de frecuencia
     */
    public static function getFrequencyOptions()
    {
        return [
            'unico' => 'Único',
            'diario' => 'Diario',
            'semanal' => 'Semanal',
            'quincenal' => 'Quincenal',
            'mensual' => 'Mensual',
            'bimestral' => 'Bimestral',
            'trimestral' => 'Trimestral',
            'semestral' => 'Semestral',
            'anual' => 'Anual',
        ];
    }

    /**
     * Calcula el monto prorrateado mensual
     */
    public function getMonthlyAmount()
    {
        switch ($this->frequency) {
            case 'diario':
                return $this->amount * 30; // Aproximación mensual
            case 'semanal':
                return $this->amount * 4.33; // Aproximación mensual (52/12)
            case 'quincenal':
                return $this->amount * 2;
            case 'mensual':
                return $this->amount;
            case 'bimestral':
                return $this->amount / 2;
            case 'trimestral':
                return $this->amount / 3;
            case 'semestral':
                return $this->amount / 6;
            case 'anual':
                return $this->amount / 12;
            case 'unico':
            default:
                return $this->amount; // Para gastos únicos, retorna el monto completo
        }
    }

    /**
     * Genera una clave única para el gasto
     */
    public function generateKey()
    {
        $business = \backend\helpers\RedisKeys::getBusiness();
        $this->business_id = $business->id;
        
        // Generar clave basada en el nombre y fecha (si es recurrente) o solo nombre
        $baseKey = strtoupper(substr(str_replace([' ', 'á', 'é', 'í', 'ó', 'ú', 'ñ'], ['_', 'A', 'E', 'I', 'O', 'U', 'N'], $this->name), 0, 10));
        
        if ($this->is_recurring && $this->expense_date) {
            $dateKey = date('Ymd', strtotime($this->expense_date));
            $key = $baseKey . '_' . $dateKey;
        } else {
            $key = $baseKey;
        }
        
        $counter = 1;
        
        // Verificar si ya existe y agregar contador si es necesario
        while (self::find()->where(['key' => $key, 'business_id' => $this->business_id])->andWhere(['!=', 'id', $this->id])->exists()) {
            if ($this->is_recurring && $this->expense_date) {
                $key = $baseKey . '_' . date('Ymd', strtotime($this->expense_date)) . '_' . $counter;
            } else {
                $key = $baseKey . '_' . $counter;
            }
            $counter++;
        }
        
        $this->key = $key;
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if (empty($this->key)) {
                $this->generateKey();
            }
            return true;
        }
        return false;
    }
}
