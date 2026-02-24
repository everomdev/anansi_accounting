<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "requisition_config".
 *
 * @property int $id
 * @property int $business_id
 * @property int $max_future_days
 * @property float $availability_green_threshold
 * @property float $availability_yellow_threshold
 * @property boolean $enable_frequent_combos
 * @property boolean $enable_auto_suggestions
 * @property boolean $require_observations_without_requisition
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Business $business
 */
class RequisitionConfig extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'requisition_config';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['business_id'], 'required'],
            [['business_id', 'max_future_days', 'created_at', 'updated_at'], 'integer'],
            [['availability_green_threshold', 'availability_yellow_threshold'], 'number'],
            [['enable_frequent_combos', 'enable_auto_suggestions', 'require_observations_without_requisition'], 'boolean'],
            [['business_id'], 'exist', 'skipOnError' => true, 'targetClass' => Business::class, 'targetAttribute' => ['business_id' => 'id']],
            [['max_future_days'], 'integer', 'min' => 1, 'max' => 90],
            [['availability_green_threshold'], 'number', 'min' => 0, 'max' => 100],
            [['availability_yellow_threshold'], 'number', 'min' => 0, 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'business_id' => 'Negocio',
            'max_future_days' => 'Días máximos a futuro',
            'availability_green_threshold' => 'Umbral verde (%)',
            'availability_yellow_threshold' => 'Umbral amarillo (%)',
            'enable_frequent_combos' => 'Habilitar combos frecuentes',
            'enable_auto_suggestions' => 'Habilitar sugerencias automáticas',
            'require_observations_without_requisition' => 'Observaciones obligatorias sin requisición',
            'created_at' => 'Creado',
            'updated_at' => 'Actualizado',
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
     * Obtiene la configuración de requisiciones para un negocio
     * Crea una configuración por defecto si no existe
     *
     * @param int $businessId
     * @return RequisitionConfig
     */
    public static function getForBusiness($businessId)
    {
        $config = static::findOne(['business_id' => $businessId]);
        
        if (!$config) {
            $config = new static([
                'business_id' => $businessId,
                'max_future_days' => 7,
                'availability_green_threshold' => 70.00,
                'availability_yellow_threshold' => 100.00,
                'enable_frequent_combos' => true,
                'enable_auto_suggestions' => true,
                'require_observations_without_requisition' => true,
            ]);
            $config->save();
        }
        
        return $config;
    }

    /**
     * Calcula el estado de disponibilidad basado en los umbrales configurados
     *
     * @param float $requestedQuantity
     * @param float $availableQuantity
     * @return array ['status' => 'available|warning|insufficient', 'percentage' => float, 'icon' => emoji]
     */
    public function calculateAvailability($requestedQuantity, $availableQuantity)
    {
        if ($availableQuantity <= 0) {
            return [
                'status' => 'insufficient',
                'percentage' => 0,
                'icon' => '🔴',
                'class' => 'danger'
            ];
        }
        
        $percentage = ($requestedQuantity / $availableQuantity) * 100;
        
        // Limitar el porcentaje a 999.99 para evitar overflow en la base de datos
        // (la columna availability_percentage tiene un límite)
        $percentage = min($percentage, 999.99);
        
        if ($percentage <= $this->availability_green_threshold) {
            return [
                'status' => 'available',
                'percentage' => $percentage,
                'icon' => '🟢',
                'class' => 'success'
            ];
        } elseif ($percentage <= $this->availability_yellow_threshold) {
            return [
                'status' => 'warning',
                'percentage' => $percentage,
                'icon' => '🟡',
                'class' => 'warning'
            ];
        } else {
            return [
                'status' => 'insufficient',
                'percentage' => $percentage,
                'icon' => '🔴',
                'class' => 'danger'
            ];
        }
    }

    /**
     * Valida que la fecha requerida no exceda el máximo de días a futuro
     *
     * @param string $requiredDate Fecha en formato Y-m-d
     * @param string $timezone Zona horaria del cliente
     * @return array ['valid' => bool, 'message' => string, 'maxDate' => string]
     */
    public function validateFutureDate($requiredDate, $timezone = null)
    {
        $tz = $timezone ?: 'UTC';
        
        try {
            $now = new \DateTime('now', new \DateTimeZone($tz));
            $required = new \DateTime($requiredDate, new \DateTimeZone($tz));
            $maxDate = (clone $now)->modify("+{$this->max_future_days} days");
            
            if ($required > $maxDate) {
                return [
                    'valid' => false,
                    'message' => "La fecha requerida no puede ser mayor a {$this->max_future_days} días en el futuro. Máximo: " . $maxDate->format('Y-m-d'),
                    'maxDate' => $maxDate->format('Y-m-d')
                ];
            }
            
            return [
                'valid' => true,
                'message' => 'Fecha válida',
                'maxDate' => $maxDate->format('Y-m-d')
            ];
        } catch (\Exception $e) {
            return [
                'valid' => false,
                'message' => 'Formato de fecha inválido',
                'maxDate' => null
            ];
        }
    }
}
