<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "empleados".
 *
 * @property int $id
 * @property int $business_id
 * @property string $nombre
 * @property string $apellido
 * @property string $puesto
 * @property string $area
 * @property string $fecha_ingreso
 * @property string|null $fecha_salida
 * @property string $estado
 * @property string|null $telefono
 * @property string|null $email
 * @property string|null $direccion
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Business $business
 * @property DocumentoEmpleado[] $documentos
 */
class Empleado extends ActiveRecord
{
    const ESTADO_ACTIVO = 'activo';
    const ESTADO_INACTIVO = 'inactivo';

    // Clasificación de documentos
    const DOCUMENTOS_CRITICOS_LEGALES = [
        'identificacion',
        'numero_seguro_social',
        'contrato',
    ];

    const DOCUMENTOS_CRITICOS_OPERATIVOS = [
        'curso_higiene',
        'induccion',
    ];

    const DOCUMENTOS_DESEABLES = [
        'comprobante_domicilio',
        'estudios',
        'cartas_recomendacion',
        'acta_nacimiento',
        'identificacion_fotografia',
        'comprobante_cursos',
        'test_personalidad',
        'test_psicometrico',
        'clabe',
        'constancia_situacion_fiscal',
        'curp',
    ];

    const SEMAFORO_VERDE = 'verde';
    const SEMAFORO_AMARILLO = 'amarillo';
    const SEMAFORO_ROJO = 'rojo';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'empleados';
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
            [['business_id', 'nombre', 'apellido', 'puesto', 'area', 'fecha_ingreso'], 'required'],
            [['business_id'], 'integer'],
            [['fecha_ingreso', 'fecha_salida'], 'safe'],
            [['estado'], 'string'],
            [['estado'], 'in', 'range' => [self::ESTADO_ACTIVO, self::ESTADO_INACTIVO]],
            [['direccion'], 'string'],
            [['nombre', 'apellido', 'puesto', 'area', 'email'], 'string', 'max' => 100],
            [['telefono'], 'string', 'max' => 20],
            [['email'], 'email'],
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
            'nombre' => 'Nombre',
            'apellido' => 'Apellido',
            'puesto' => 'Puesto',
            'area' => 'Área',
            'fecha_ingreso' => 'Fecha de Ingreso',
            'fecha_salida' => 'Fecha de Salida',
            'estado' => 'Estado',
            'telefono' => 'Teléfono',
            'email' => 'Email',
            'direccion' => 'Dirección',
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
     * Gets query for [[Documentos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDocumentos()
    {
        return $this->hasMany(DocumentoEmpleado::class, ['empleado_id' => 'id']);
    }

    /**
     * Obtiene el nombre completo del empleado
     *
     * @return string
     */
    public function getNombreCompleto()
    {
        return $this->nombre . ' ' . $this->apellido;
    }

    /**
     * Calcula el estado del semáforo del expediente
     *
     * @return string
     */
    public function getSemaforoExpediente()
    {
        $documentosCargados = $this->getDocumentos()
            ->select('tipo_documento')
            ->column();

        // Obtener todos los tipos de documentos
        $todosLosDocumentos = array_merge(
            self::DOCUMENTOS_CRITICOS_LEGALES,
            self::DOCUMENTOS_CRITICOS_OPERATIVOS,
            self::DOCUMENTOS_DESEABLES
        );

        // Verificar documentos críticos legales
        $faltanCriticosLegales = array_diff(self::DOCUMENTOS_CRITICOS_LEGALES, $documentosCargados);
        if (!empty($faltanCriticosLegales)) {
            return self::SEMAFORO_ROJO;
        }

        // Verificar documentos críticos operativos
        $faltanCriticosOperativos = array_diff(self::DOCUMENTOS_CRITICOS_OPERATIVOS, $documentosCargados);
        if (!empty($faltanCriticosOperativos)) {
            return self::SEMAFORO_ROJO;
        }

        // Verificar si todos los documentos están cargados
        $faltanDocumentos = array_diff($todosLosDocumentos, $documentosCargados);
        if (empty($faltanDocumentos)) {
            return self::SEMAFORO_VERDE;
        }

        // Si solo faltan documentos deseables
        return self::SEMAFORO_AMARILLO;
    }

    /**
     * Obtiene el color del badge según el semáforo
     *
     * @return string
     */
    public function getSemaforoBadgeClass()
    {
        $semaforo = $this->getSemaforoExpediente();
        
        switch ($semaforo) {
            case self::SEMAFORO_VERDE:
                return 'bg-success';
            case self::SEMAFORO_AMARILLO:
                return 'bg-warning';
            case self::SEMAFORO_ROJO:
                return 'bg-danger';
            default:
                return 'bg-secondary';
        }
    }

    /**
     * Obtiene el texto del semáforo
     *
     * @return string
     */
    public function getSemaforoTexto()
    {
        $semaforo = $this->getSemaforoExpediente();
        
        switch ($semaforo) {
            case self::SEMAFORO_VERDE:
                return 'Completo';
            case self::SEMAFORO_AMARILLO:
                return 'Incompleto';
            case self::SEMAFORO_ROJO:
                return 'Crítico';
            default:
                return 'Sin documentos';
        }
    }

    /**
     * Obtiene los documentos faltantes
     *
     * @return array
     */
    public function getDocumentosFaltantes()
    {
        $documentosCargados = $this->getDocumentos()
            ->select('tipo_documento')
            ->column();

        $todosLosDocumentos = array_merge(
            self::DOCUMENTOS_CRITICOS_LEGALES,
            self::DOCUMENTOS_CRITICOS_OPERATIVOS,
            self::DOCUMENTOS_DESEABLES
        );

        return array_diff($todosLosDocumentos, $documentosCargados);
    }

    /**
     * Obtiene el porcentaje de completitud del expediente
     *
     * @return float
     */
    public function getPorcentajeCompletitud()
    {
        $todosLosDocumentos = array_merge(
            self::DOCUMENTOS_CRITICOS_LEGALES,
            self::DOCUMENTOS_CRITICOS_OPERATIVOS,
            self::DOCUMENTOS_DESEABLES
        );

        $total = count($todosLosDocumentos);
        $cargados = $this->getDocumentos()->count();

        if ($total == 0) {
            return 0;
        }

        return round(($cargados / $total) * 100, 2);
    }

    /**
     * Obtiene todos los tipos de documentos con etiquetas
     *
     * @return array
     */
    public static function getTiposDocumentosLabels()
    {
        return [
            'identificacion' => 'Identificación (INE/IFE)',
            'numero_seguro_social' => 'Número de Seguro Social',
            'contrato' => 'Contrato',
            'curso_higiene' => 'Curso de Higiene',
            'induccion' => 'Inducción',
            'comprobante_domicilio' => 'Comprobante de Domicilio',
            'estudios' => 'Comprobante de Estudios',
            'cartas_recomendacion' => 'Cartas de Recomendación',
            'acta_nacimiento' => 'Acta de Nacimiento',
            'identificacion_fotografia' => 'Fotografía de Identificación',
            'comprobante_cursos' => 'Comprobante de Cursos',
            'test_personalidad' => 'Test de Personalidad',
            'test_psicometrico' => 'Test Psicométrico',
            'clabe' => 'Clabe de Banca Electrónica',
            'constancia_situacion_fiscal' => 'Constancia de Situación Fiscal',
            'curp' => 'CURP',
        ];
    }

    /**
     * Verifica si un documento es crítico
     *
     * @param string $tipoDocumento
     * @return bool
     */
    public static function esDocumentoCritico($tipoDocumento)
    {
        return in_array($tipoDocumento, array_merge(
            self::DOCUMENTOS_CRITICOS_LEGALES,
            self::DOCUMENTOS_CRITICOS_OPERATIVOS
        ));
    }

    /**
     * Obtiene la clasificación de un documento
     *
     * @param string $tipoDocumento
     * @return string
     */
    public static function getClasificacionDocumento($tipoDocumento)
    {
        if (in_array($tipoDocumento, self::DOCUMENTOS_CRITICOS_LEGALES)) {
            return 'Crítico Legal';
        }
        
        if (in_array($tipoDocumento, self::DOCUMENTOS_CRITICOS_OPERATIVOS)) {
            return 'Crítico Operativo';
        }
        
        return 'Deseable';
    }
}
