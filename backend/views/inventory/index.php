
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
        <?= Html::a('Descargar plantilla', ['export-plantilla-inventario'], [
            'class' => 'btn btn-success',
            'style' => 'margin-left:12px;',
            'title' => 'Descargar plantilla de inventario en Excel',
        ]) ?>
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
echo \yii\bootstrap5\Html::submitButton(Yii::t('app', "Import"), [
    'class' => 'btn btn-success'
]);

\yii\bootstrap5\ActiveForm::end();

\yii\bootstrap5\Modal::end();
?>
