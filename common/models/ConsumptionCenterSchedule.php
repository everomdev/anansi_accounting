<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "consumption_center_schedule".
 *
 * @property int $id
 * @property int $consumption_center_id
 * @property int $day_of_week (0=Domingo, 1=Lunes, ..., 6=Sábado)
 * @property string $start_time
 * @property string $end_time
 * @property bool $is_active
 * @property string $created_at
 * @property string $updated_at
 *
 * @property ConsumptionCenter $consumptionCenter
 */
class ConsumptionCenterSchedule extends ActiveRecord
{
    // Constantes para días de la semana
    const DAY_SUNDAY = 0;
    const DAY_MONDAY = 1;
    const DAY_TUESDAY = 2;
    const DAY_WEDNESDAY = 3;
    const DAY_THURSDAY = 4;
    const DAY_FRIDAY = 5;
    const DAY_SATURDAY = 6;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'consumption_center_schedule';
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
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['consumption_center_id', 'day_of_week', 'start_time', 'end_time'], 'required'],
            [['consumption_center_id', 'day_of_week'], 'integer'],
            [['day_of_week'], 'in', 'range' => [0, 1, 2, 3, 4, 5, 6]],
            [['is_active'], 'boolean'],
            [['start_time', 'end_time'], 'string', 'max' => 5],
            [['start_time', 'end_time'], 'match', 'pattern' => '/^([01]\d|2[0-3]):([0-5]\d)$/', 'message' => 'El formato debe ser HH:MM (24 horas)'],
            [['created_at', 'updated_at'], 'safe'],
            [['consumption_center_id'], 'exist', 'skipOnError' => true, 'targetClass' => ConsumptionCenter::class, 'targetAttribute' => ['consumption_center_id' => 'id']],
            // NOTA: Validación única eliminada - ahora se permiten múltiples horarios por día
            // Esto permite configurar múltiples rangos de horarios para el mismo día
            // Por ejemplo: Lunes 7:00-8:30 y 13:00-14:30
            // [['day_of_week'], 'unique', 'targetAttribute' => ['consumption_center_id', 'day_of_week'], 'message' => 'Ya existe un horario para este día en este centro de consumo.'],
            // Validar que end_time sea mayor que start_time
            ['end_time', 'validateTimeRange'],
        ];
    }

    /**
     * Validar que la hora de fin sea mayor que la hora de inicio
     */
    public function validateTimeRange($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $start = strtotime($this->start_time);
            $end = strtotime($this->end_time);
            
            if ($end <= $start) {
                $this->addError($attribute, 'La hora de fin debe ser mayor que la hora de inicio.');
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'consumption_center_id' => Yii::t('app', 'Centro de Consumo'),
            'day_of_week' => Yii::t('app', 'Día de la Semana'),
            'start_time' => Yii::t('app', 'Hora de Inicio'),
            'end_time' => Yii::t('app', 'Hora de Fin'),
            'is_active' => Yii::t('app', 'Activo'),
            'created_at' => Yii::t('app', 'Creado'),
            'updated_at' => Yii::t('app', 'Actualizado'),
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
     * Obtener el nombre del día en español
     * 
     * @return string
     */
    public function getDayName()
    {
        return self::getDayNames()[$this->day_of_week] ?? '';
    }

    /**
     * Obtener array de nombres de días
     * 
     * @return array
     */
    public static function getDayNames()
    {
        return [
            self::DAY_SUNDAY => 'Domingo',
            self::DAY_MONDAY => 'Lunes',
            self::DAY_TUESDAY => 'Martes',
            self::DAY_WEDNESDAY => 'Miércoles',
            self::DAY_THURSDAY => 'Jueves',
            self::DAY_FRIDAY => 'Viernes',
            self::DAY_SATURDAY => 'Sábado',
        ];
    }

    /**
     * Obtener array de nombres de días cortos
     * 
     * @return array
     */
    public static function getDayShortNames()
    {
        return [
            self::DAY_SUNDAY => 'Dom',
            self::DAY_MONDAY => 'Lun',
            self::DAY_TUESDAY => 'Mar',
            self::DAY_WEDNESDAY => 'Mié',
            self::DAY_THURSDAY => 'Jue',
            self::DAY_FRIDAY => 'Vie',
            self::DAY_SATURDAY => 'Sáb',
        ];
    }

    /**
     * Verificar si un horario está dentro del rango permitido
     * 
     * @param string $time Hora en formato HH:MM
     * @return bool
     */
    public function isTimeInRange($time)
    {
        if (!$this->is_active) {
            return false;
        }

        $checkTime = strtotime($time);
        $startTime = strtotime($this->start_time);
        $endTime = strtotime($this->end_time);

        return $checkTime >= $startTime && $checkTime <= $endTime;
    }

    /**
     * Obtener horarios formateados para mostrar
     * 
     * @return string
     */
    public function getFormattedSchedule()
    {
        return "{$this->start_time} - {$this->end_time}";
    }
}
