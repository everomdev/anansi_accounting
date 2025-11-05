
<?php
use yii\grid\GridView;
use yii\helpers\Html;

$this->title = 'Inventario de Insumos';
$this->params['breadcrumbs'][] = $this->title;
$businessData = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);
$business = \common\models\Business::findOne(['id' => $businessData['id']]);

?>
<div class="inventory-index">
    <p>
        <?= Html::a('Crear Inventario', ['create'], ['class' => 'btn btn-success']) ?>
        <button id="descargar-plantilla-btn" class="btn btn-success" style="margin-left:12px;" title="Descargar plantilla de inventario en Excel">
            Descargar plantilla
        </button>
         <?= \yii\bootstrap5\Html::a(Yii::t('app', '{icon} Cargar inventario', [
                'icon' => ""
            ]), '#', ['class' => 'btn btn-warning', 'data-bs-toggle' => 'modal', 'data-bs-target' => "#modal-upload-file"]) ?>
    </p>
    <?php
    // Mostrar todas las fechas únicas completas (con hora) de toda la tabla
    $fechas = \common\models\Inventory::find()
        ->select('fecha')
        ->where(['business_id' => $business->id])
        ->distinct()
        ->orderBy(['fecha' => SORT_DESC])
        ->column();
    ?>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Fecha de inventario</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($fechas as $fechaRaw): ?>
                <tr>
                    <td><?= date('d/m/Y H:i', strtotime($fechaRaw)) ?></td>
                    <td>
                        <?= Html::a('<span class="glyphicon glyphicon-eye-open"></span> Ver detalles', ['inventory/detalle', 'fecha' => $fechaRaw], ['class' => 'btn btn-info btn-sm']) ?>
                        <?php if (Yii::$app->user->can('storage_admin') || Yii::$app->user->can('manage_users') || Yii::$app->user->can('administrator')): ?>
                            <?= Html::a('<span class="glyphicon glyphicon-pencil"></span> Editar inventario', ['inventory/edit', 'fecha' => $fechaRaw], ['class' => 'btn btn-warning btn-sm', 'style' => 'margin-left: 8px;']) ?>
                            <?= Html::a('<span class="glyphicon glyphicon-trash"></span> Eliminar', ['inventory/delete-by-fecha', 'fecha' => $fechaRaw], [
                                'class' => 'btn btn-danger btn-sm',
                                'style' => 'margin-left: 8px;',
                                'data' => [
                                    'confirm' => '¿Estás seguro de que quieres eliminar todo el inventario del ' . date('d/m/Y H:i', strtotime($fechaRaw)) . '? Esta acción no se puede deshacer.',
                                    'method' => 'post',
                                ],
                            ]) ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php
\yii\bootstrap5\Modal::begin([
    'id' => 'modal-upload-file',
    'title' => Yii::t('app', "Importar inventario")
]);
$url = \yii\helpers\Url::to(['inventory/import-plantilla-inventario']);
\yii\bootstrap5\ActiveForm::begin([
    'action' => $url,
    'method' => 'post',
    'options' => [
        'enctype' => 'multipart/form-data'
    ]
]);

echo \yii\bootstrap5\Html::input('file', 'inventory-file', '', [
    'class' => 'form-control',
    'accept' => '.xlsx,.xls,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel'
]);
echo "<br>";
echo \yii\bootstrap5\Html::hiddenInput('fecha_importacion', '', ['id' => 'fecha-importacion-hidden']);
echo \yii\bootstrap5\Html::button(Yii::t('app', "Import"), [
    'id' => 'import-btn',
    'class' => 'btn btn-success',
    'type' => 'button'
]);

\yii\bootstrap5\ActiveForm::end();

\yii\bootstrap5\Modal::end();

// Modal de confirmación para descarga de plantilla
\yii\bootstrap5\Modal::begin([
    'id' => 'modal-confirmacion-descarga',
    'title' => '⚠️ Atención - Validez de Plantilla',
    'size' => \yii\bootstrap5\Modal::SIZE_DEFAULT,
    'options' => [
        'class' => 'custom-inventory-modal'
    ]
]);
?>
<style>
.custom-inventory-modal .modal-content {
    background-color: #F5F5F5;
    color: #333333;
}
.custom-inventory-modal .modal-header {
    background-color: #F5F5F5;
    border-bottom: 1px solid #ddd;
}
.custom-inventory-modal .modal-header .modal-title {
    color: #333333;
    font-weight: 600;
}
.custom-inventory-modal .modal-body .alert {
    background-color: #E1A948;
    border-color: #C68B2C;
    color: #333333;
}
.custom-inventory-modal .modal-body .alert strong {
    color: #D64541;
}
.custom-inventory-modal .modal-body p {
    color: #333333;
}
.custom-inventory-modal .modal-footer {
    background-color: #F5F5F5;
    border-top: 1px solid #ddd;
}
.custom-inventory-modal .modal-footer .btn-secondary {
    background-color: #6E7A8A;
    border-color: #6E7A8A;
    color: white;
}
.custom-inventory-modal .modal-footer .btn-secondary:hover {
    background-color: #5a6473;
    border-color: #5a6473;
}
.custom-inventory-modal .modal-footer .btn-success {
    background-color: #C68B2C;
    border-color: #C68B2C;
    color: white;
}
.custom-inventory-modal .modal-footer .btn-success:hover {
    background-color: #b17a26;
    border-color: #b17a26;
}
.custom-inventory-modal .btn-close {
    filter: invert(1) grayscale(100%) brightness(200%);
}
</style>
<div class="alert alert-warning" role="alert">
    <p>Esta plantilla de inventario tiene una <strong>validez máxima de 48 horas</strong> a partir de su fecha y hora de generación.</p>
    <p>Si intentas cargarla después de ese tiempo, <strong>el sistema no la aceptará</strong>.</p>
    <hr>
    <p class="mb-0"><strong>Recomendación:</strong> Te sugerimos levantar y cargar el inventario el mismo día para asegurar datos correctos.</p>
</div>
<div class="text-end">
    <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancelar</button>
    <button type="button" class="btn btn-success" id="confirmar-descarga-btn">Entendido, descargar plantilla</button>
</div>
<?php
\yii\bootstrap5\Modal::end();
?>

<script>
// Función para obtener fecha local del usuario
function getFechaLocal() {
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    
    return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
}

// Botón descargar plantilla - mostrar modal de confirmación
document.getElementById('descargar-plantilla-btn').addEventListener('click', function() {
    // Mostrar el modal de confirmación
    const modal = new bootstrap.Modal(document.getElementById('modal-confirmacion-descarga'));
    modal.show();
});

// Botón confirmar descarga en el modal
document.getElementById('confirmar-descarga-btn').addEventListener('click', function() {
    const fechaLocal = getFechaLocal().slice(0, -3); // Sin segundos para la plantilla
    
    // Cerrar el modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('modal-confirmacion-descarga'));
    modal.hide();
    
    // Proceder con la descarga
    window.location.href = '<?= \yii\helpers\Url::to(['export-plantilla-inventario']) ?>?fecha=' + encodeURIComponent(fechaLocal);
});

// Botón importar archivo
document.getElementById('import-btn').addEventListener('click', function() {
    const fileInput = document.querySelector('input[name="inventory-file"]');
    if (!fileInput.files.length) {
        alert('Por favor selecciona un archivo para importar.');
        return;
    }
    
    // Cambiar el botón a estado de cargando
    const importBtn = this;
    const originalText = importBtn.innerHTML;
    const originalClass = importBtn.className;
    
    importBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Importando...';
    importBtn.className = 'btn btn-secondary';
    importBtn.disabled = true;
    
    // Deshabilitar también el botón de cancelar
    const cancelBtn = document.querySelector('#modal-upload-file .btn-secondary');
    if (cancelBtn) {
        cancelBtn.disabled = true;
    }
    
    // Establecer la fecha local en el campo oculto
    document.getElementById('fecha-importacion-hidden').value = getFechaLocal();
    
    // Enviar el formulario
    fileInput.closest('form').submit();
    
    // Opcional: restaurar el botón después de un tiempo en caso de error
    setTimeout(function() {
        importBtn.innerHTML = originalText;
        importBtn.className = originalClass;
        importBtn.disabled = false;
        if (cancelBtn) {
            cancelBtn.disabled = false;
        }
    }, 10000); // 10 segundos como fallback
});
</script>
