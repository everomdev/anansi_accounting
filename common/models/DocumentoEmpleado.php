<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\web\UploadedFile;

/**
 * This is the model class for table "documentos_empleado".
 *
 * @property int $id
 * @property int $empleado_id
 * @property string $tipo_documento
 * @property string $archivo
 * @property string $nombre_original
 * @property string $fecha_carga
 * @property string|null $fecha_vencimiento
 * @property string|null $observaciones
 * @property int|null $uploaded_by
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Empleado $empleado
 * @property User $uploadedBy
 */
class DocumentoEmpleado extends ActiveRecord
{
    public $archivoFile;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'documentos_empleado';
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
            [['empleado_id', 'tipo_documento'], 'required'],
            [['empleado_id', 'uploaded_by'], 'integer'],
            [['tipo_documento', 'observaciones'], 'string'],
            [['fecha_carga', 'fecha_vencimiento'], 'safe'],
            [['archivo', 'nombre_original'], 'string', 'max' => 255],
            [['archivoFile'], 'file', 'skipOnEmpty' => true, 'extensions' => 'pdf, jpg, jpeg, png, doc, docx'],
            [['empleado_id'], 'exist', 'skipOnError' => true, 'targetClass' => Empleado::class, 'targetAttribute' => ['empleado_id' => 'id']],
            [['uploaded_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['uploaded_by' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'empleado_id' => 'Empleado',
            'tipo_documento' => 'Tipo de Documento',
            'archivo' => 'Archivo',
            'nombre_original' => 'Nombre Original',
            'fecha_carga' => 'Fecha de Carga',
            'fecha_vencimiento' => 'Fecha de Vencimiento',
            'observaciones' => 'Observaciones',
            'uploaded_by' => 'Subido Por',
            'created_at' => 'Creado',
            'updated_at' => 'Actualizado',
            'archivoFile' => 'Archivo',
        ];
    }

    /**
     * Gets query for [[Empleado]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEmpleado()
    {
        return $this->hasOne(Empleado::class, ['id' => 'empleado_id']);
    }

    /**
     * Gets query for [[UploadedBy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUploadedBy()
    {
        return $this->hasOne(User::class, ['id' => 'uploaded_by']);
    }

    /**
     * Sube el archivo y guarda la ruta
     *
     * @return bool
     */
    public function upload()
    {
        if ($this->archivoFile) {
            $uploadsPath = Yii::getAlias('@backend/web/uploads/documentos-empleados/');
            
            // Crear directorio si no existe
            if (!file_exists($uploadsPath)) {
                mkdir($uploadsPath, 0777, true);
            }
            
            // Generar nombre único para el archivo
            $fileName = uniqid() . '_' . time() . '.' . $this->archivoFile->extension;
            $filePath = $uploadsPath . $fileName;
            
            if ($this->archivoFile->saveAs($filePath)) {
                $this->nombre_original = $this->archivoFile->baseName . '.' . $this->archivoFile->extension;
                $this->archivo = $fileName;
                $this->fecha_carga = date('Y-m-d H:i:s');
                $this->uploaded_by = Yii::$app->user->id;
                return true;
            }
        }
        return false;
    }

    /**
     * Obtiene la ruta completa del archivo
     *
     * @return string
     */
    public function getArchivoPath()
    {
        return Yii::getAlias('@backend/web/uploads/documentos-empleados/') . $this->archivo;
    }

    /**
     * Obtiene la URL del archivo
     *
     * @return string
     */
    public function getArchivoUrl()
    {
        return Yii::getAlias('@web/uploads/documentos-empleados/') . $this->archivo;
    }

    /**
     * Verifica si el documento está vencido
     *
     * @return bool
     */
    public function estaVencido()
    {
        if ($this->fecha_vencimiento) {
            return strtotime($this->fecha_vencimiento) < time();
        }
        return false;
    }

    /**
     * Obtiene el label del tipo de documento
     *
     * @return string
     */
    public function getTipoDocumentoLabel()
    {
        $labels = Empleado::getTiposDocumentosLabels();
        return $labels[$this->tipo_documento] ?? $this->tipo_documento;
    }

    /**
     * Obtiene el icono según la extensión del archivo
     *
     * @return string
     */
    public function getFileIcon()
    {
        $extension = pathinfo($this->archivo, PATHINFO_EXTENSION);
        
        switch (strtolower($extension)) {
            case 'pdf':
                return 'bx bxs-file-pdf text-danger';
            case 'doc':
            case 'docx':
                return 'bx bxs-file-doc text-primary';
            case 'jpg':
            case 'jpeg':
            case 'png':
                return 'bx bxs-image text-success';
            default:
                return 'bx bxs-file text-secondary';
        }
    }

    /**
     * Elimina el archivo físico antes de eliminar el registro
     *
     * @return bool
     */
    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            $filePath = $this->getArchivoPath();
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            return true;
        }
        return false;
    }
}
