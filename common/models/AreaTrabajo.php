<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "area_trabajo".
 *
 * @property int $id
 * @property int $business_id
 * @property string $nombre
 * @property string|null $descripcion
 * @property int $orden
 * @property string $estado
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Business $business
 * @property PlantillaPuesto[] $puestos
 * @property Empleado[] $empleados
 */
class AreaTrabajo extends ActiveRecord
{
    const ESTADO_ACTIVO = 'activo';
    const ESTADO_INACTIVO = 'inactivo';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'area_trabajo';
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
            [['business_id', 'nombre'], 'required'],
            [['business_id', 'orden'], 'integer'],
            [['descripcion'], 'string'],
            [['nombre'], 'string', 'max' => 100],
            [['estado'], 'string', 'max' => 20],
            [['estado'], 'in', 'range' => [self::ESTADO_ACTIVO, self::ESTADO_INACTIVO]],
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
            'business_id' => 'Negocio',
            'nombre' => 'Nombre del Área',
            'descripcion' => 'Descripción',
            'orden' => 'Orden',
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
     * Gets query for [[Puestos]].
     */
    public function getPuestos()
    {
        return $this->hasMany(PlantillaPuesto::class, ['area_trabajo_id' => 'id'])
            ->where(['estado' => PlantillaPuesto::ESTADO_ACTIVO]);
    }

    /**
     * Gets query for [[Empleados]] - empleados que pertenecen a esta área
     */
    public function getEmpleados()
    {
        return $this->hasMany(Empleado::class, ['business_id' => 'business_id'])
            ->where(['area' => $this->nombre, 'estado' => Empleado::ESTADO_ACTIVO]);
    }

    /**
     * Obtiene el conteo de empleados actuales en esta área
     */
    public function getEmpleadosCount()
    {
        return (int) Empleado::find()
            ->where(['business_id' => $this->business_id, 'area' => $this->nombre, 'estado' => Empleado::ESTADO_ACTIVO])
            ->count();
    }

    /**
     * Obtiene el total de puestos requeridos (suma de cantidad_ideal)
     */
    public function getTotalRequerido()
    {
        return (int) PlantillaPuesto::find()
            ->where(['area_trabajo_id' => $this->id, 'estado' => PlantillaPuesto::ESTADO_ACTIVO])
            ->sum('cantidad_ideal') ?: 0;
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
