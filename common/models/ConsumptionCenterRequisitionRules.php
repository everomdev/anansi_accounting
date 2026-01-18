<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "consumption_center_requisition_rules".
 *
 * @property int $id
 * @property int $consumption_center_id
 * @property int $business_id
 * @property string|null $requisition_allowed_days JSON array de días permitidos
 * @property string|null $day_schedules JSON object con horarios por día {day: {start_time, end_time}}
 * @property string|null $requisition_start_time (DEPRECADO - usar day_schedules)
 * @property string|null $requisition_end_time (DEPRECADO - usar day_schedules)
 * @property bool $allow_extemporaneous_requisitions
 * @property bool $require_extemporaneous_reason
 * @property string $created_at
 * @property string $updated_at
 *
 * @property ConsumptionCenter $consumptionCenter
 * @property Business $business
 */
class ConsumptionCenterRequisitionRules extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'consumption_center_requisition_rules';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['consumption_center_id', 'business_id'], 'required'],
            [['consumption_center_id', 'business_id'], 'integer'],
            [['requisition_allowed_days', 'day_schedules'], 'safe'], // JSON fields
            [['requisition_start_time', 'requisition_end_time'], 'string', 'max' => 5],
            [['requisition_start_time', 'requisition_end_time'], 'match', 'pattern' => '/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', 'message' => 'Formato de hora inválido (HH:MM)'],
            [['allow_extemporaneous_requisitions', 'require_extemporaneous_reason'], 'boolean'],
            [['allow_extemporaneous_requisitions', 'require_extemporaneous_reason'], 'default', 'value' => true],
            [['created_at', 'updated_at'], 'safe'],
            [['consumption_center_id'], 'exist', 'skipOnError' => true, 'targetClass' => ConsumptionCenter::class, 'targetAttribute' => ['consumption_center_id' => 'id']],
            [['business_id'], 'exist', 'skipOnError' => true, 'targetClass' => Business::class, 'targetAttribute' => ['business_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'consumption_center_id' => Yii::t('app', 'Centro de Consumo'),
            'business_id' => 'Business ID',
            'requisition_allowed_days' => Yii::t('app', 'Días permitidos para requisiciones'),
            'requisition_start_time' => Yii::t('app', 'Hora de inicio'),
            'requisition_end_time' => Yii::t('app', 'Hora de fin'),
            'allow_extemporaneous_requisitions' => Yii::t('app', 'Permitir requisiciones extemporáneas'),
            'require_extemporaneous_reason' => Yii::t('app', 'Requiere motivo para requisiciones extemporáneas'),
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[ConsumptionCenter]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getConsumptionCenter()
    {
        return $this->hasOne(ConsumptionCenter::class, ['id' => 'consumption_center_id']);
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
     * Gets query for [[Schedules]].
     * Relación con los horarios específicos por día
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSchedules()
    {
        return $this->hasMany(ConsumptionCenterSchedule::class, ['consumption_center_id' => 'consumption_center_id'])
            ->orderBy(['day_of_week' => SORT_ASC]);
    }

    /**
     * Obtener array de días permitidos desde JSON
     * @return array
     */
    public function getRequisitionAllowedDaysArray()
    {
        if (empty($this->requisition_allowed_days)) {
            return [1, 2, 3, 4, 5]; // Default: Lunes a Viernes
        }
        
        $decoded = json_decode($this->requisition_allowed_days, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Establecer array de días permitidos como JSON
     * @param array $days
     */
    public function setRequisitionAllowedDaysArray($days)
    {
        if (is_array($days)) {
            // Convertir a enteros y ordenar
            $days = array_map('intval', $days);
            sort($days);
            $this->requisition_allowed_days = json_encode($days);
        }
    }

    /**
     * Obtener horarios por día desde JSON
     * @return array Formato: {day: {start_time, end_time}}
     */
    public function getDaySchedulesArray()
    {
        if (empty($this->day_schedules)) {
            return [];
        }
        
        $decoded = json_decode($this->day_schedules, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Establecer horarios por día como JSON
     * @param array $schedules Formato: {day: {start_time, end_time}}
     */
    public function setDaySchedulesArray($schedules)
    {
        if (is_array($schedules)) {
            $this->day_schedules = json_encode($schedules);
        }
    }

    /**
     * Obtener horario para un día específico
     * @param int $day Número del día (0=Domingo, 1=Lunes, etc)
     * @return array|null ['start_time' => string, 'end_time' => string] o null
     */
    public function getScheduleForDay($day)
    {
        $schedules = $this->getDaySchedulesArray();
        
        if (isset($schedules[$day])) {
            return $schedules[$day];
        }
        
        // Fallback a horarios globales (compatibilidad)
        if ($this->requisition_start_time && $this->requisition_end_time) {
            return [
                'start_time' => $this->requisition_start_time,
                'end_time' => $this->requisition_end_time,
            ];
        }
        
        // Default
        return [
            'start_time' => '00:00',
            'end_time' => '23:59',
        ];
    }

    /**
     * Verificar si se puede crear requisición en este momento para este centro
     * @return array ['allowed' => bool, 'reason' => string]
     */
    public function canCreateRequisitionNow()
    {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $currentDay = (int)$now->format('w'); // 0 = Domingo, 1 = Lunes, ...
        $currentTime = $now->format('H:i');
        
        // Obtener días permitidos
        $allowedDays = $this->getRequisitionAllowedDaysArray();
        
        // Verificar día
        $isDayAllowed = in_array($currentDay, $allowedDays);
        
        // Verificar hora (usar horario específico del día)
        $schedule = $this->getScheduleForDay($currentDay);
        $startTime = $schedule['start_time'] ?? '00:00';
        $endTime = $schedule['end_time'] ?? '23:59';
        $isTimeAllowed = $currentTime >= $startTime && $currentTime <= $endTime;
        
        $allowed = $isDayAllowed && $isTimeAllowed;
        
        if (!$allowed) {
            if (!$isDayAllowed && !$isTimeAllowed) {
                $reason = Yii::t('app', 'Día y horario no permitidos');
            } elseif (!$isDayAllowed) {
                $reason = Yii::t('app', 'Día no permitido');
            } else {
                $reason = Yii::t('app', 'Fuera del horario permitido ({start} - {end})', [
                    'start' => $startTime,
                    'end' => $endTime,
                ]);
            }
        } else {
            $reason = '';
        }
        
        return [
            'allowed' => $allowed,
            'reason' => $reason,
            'day_allowed' => $isDayAllowed,
            'time_allowed' => $isTimeAllowed,
        ];
    }

    /**
     * Obtener o crear reglas para un centro de consumo
     * @param int $consumptionCenterId
     * @param int $businessId
     * @return ConsumptionCenterRequisitionRules
     */
    public static function getForConsumptionCenter($consumptionCenterId, $businessId)
    {
        $rules = static::findOne(['consumption_center_id' => $consumptionCenterId]);
        
        if (!$rules) {
            $rules = new static([
                'consumption_center_id' => $consumptionCenterId,
                'business_id' => $businessId,
                'requisition_start_time' => '00:00',
                'requisition_end_time' => '23:59',
                'allow_extemporaneous_requisitions' => true,
                'require_extemporaneous_reason' => true,
            ]);
            $rules->setRequisitionAllowedDaysArray([1, 2, 3, 4, 5]); // Lunes a Viernes
            $rules->save();
        }
        
        return $rules;
    }
}
