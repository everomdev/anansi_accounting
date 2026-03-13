<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "plantilla_puesto".
 *
 * @property int $id
 * @property int $business_id
 * @property int $area_trabajo_id
 * @property string $nombre_puesto
 * @property string|null $descripcion
 * @property int $cantidad_minima
 * @property int $cantidad_ideal
 * @property float|null $salario_estimado
 * @property string $estado
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Business $business
 * @property AreaTrabajo $areaTrabajo
 */
class PlantillaPuesto extends ActiveRecord
{
    const ESTADO_ACTIVO = 'activo';
    const ESTADO_INACTIVO = 'inactivo';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'plantilla_puesto';
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
                'value' => date('Y-m-d H:i:s'),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['business_id', 'area_trabajo_id', 'nombre_puesto', 'cantidad_minima'], 'required'],
            [['business_id', 'area_trabajo_id', 'cantidad_minima', 'cantidad_ideal'], 'integer'],
            [['cantidad_minima', 'cantidad_ideal'], 'integer', 'min' => 0],
            [['descripcion'], 'string'],
            [['salario_estimado'], 'number'],
            [['nombre_puesto'], 'string', 'max' => 100],
            [['estado'], 'string', 'max' => 20],
            [['estado'], 'in', 'range' => [self::ESTADO_ACTIVO, self::ESTADO_INACTIVO]],
            [['business_id'], 'exist', 'skipOnError' => true, 'targetClass' => Business::class, 'targetAttribute' => ['business_id' => 'id']],
            [['area_trabajo_id'], 'exist', 'skipOnError' => true, 'targetClass' => AreaTrabajo::class, 'targetAttribute' => ['area_trabajo_id' => 'id']],
            // Validación: cantidad_ideal debe ser >= cantidad_minima
            ['cantidad_ideal', 'compare', 'compareAttribute' => 'cantidad_minima', 'operator' => '>=', 'message' => 'La cantidad ideal debe ser mayor o igual a la cantidad mínima.'],
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
            'area_trabajo_id' => 'Área de Trabajo',
            'nombre_puesto' => 'Nombre del Puesto',
            'descripcion' => 'Descripción',
            'cantidad_minima' => 'Cantidad Mínima',
            'cantidad_ideal' => 'Cantidad Ideal',
            'salario_estimado' => 'Salario Estimado',
            'estado' => 'Estado',
            'created_at' => 'Creado',
            'updated_at' => 'Actualizado',
        ];
    }

    /**
     * Gets query for [[Business]].
     */
    public function getBusiness()
    {
        return $this->hasOne(Business::class, ['id' => 'business_id']);
    }

    /**
     * Gets query for [[AreaTrabajo]].
     */
    public function getAreaTrabajo()
    {
        return $this->hasOne(AreaTrabajo::class, ['id' => 'area_trabajo_id']);
    }

    /**
     * Obtiene el nombre del área
     */
    public function getAreaNombre()
    {
        return $this->areaTrabajo ? $this->areaTrabajo->nombre : '';
    }

    /**
     * Obtiene la cantidad actual de empleados con este puesto en esta área
     */
    public function getEmpleadosActualesCount()
    {
        return (int) Empleado::find()
            ->where([
                'business_id' => $this->business_id,
                'puesto' => $this->nombre_puesto,
                'area' => $this->getAreaNombre(),
                'estado' => Empleado::ESTADO_ACTIVO
            ])
            ->count();
    }

    /**
     * Obtiene el semáforo del puesto (verde/amarillo/rojo)
     */
    public function getSemaforoEstado()
    {
        $actual = $this->getEmpleadosActualesCount();
        
        if ($actual >= $this->cantidad_ideal) {
            return 'verde';
        } elseif ($actual >= $this->cantidad_minima) {
            return 'amarillo';
        } else {
            return 'rojo';
        }
    }

    /**
     * Obtiene la clase de badge según el semáforo
     */
    public function getSemaforoBadgeClass()
    {
        $estado = $this->getSemaforoEstado();
        
        return [
            'verde' => 'bg-success',
            'amarillo' => 'bg-warning',
            'rojo' => 'bg-danger',
        ][$estado];
    }

    /**
     * Obtiene el texto del semáforo
     */
    public function getSemaforoTexto()
    {
        $estado = $this->getSemaforoEstado();
        
        return [
            'verde' => 'Completo',
            'amarillo' => 'Incompleto',
            'rojo' => 'Crítico',
        ][$estado];
    }

    /**
     * Obtiene estados disponibles
     */
    public static function getEstadosArray()
    {
        return [
            self::ESTADO_ACTIVO => 'Activo',
            self::ESTADO_INACTIVO => 'Inactivo',
        ];
    }
}
