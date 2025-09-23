
<?php
use yii\grid\GridView;
use yii\helpers\Html;

$this->title = 'Inventario de Insumos';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="inventory-index">
    <p>
        <?= Html::a('Crear Inventario', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?php
    // Agrupar fechas únicas
    $fechas = [];
    foreach ($dataProvider->getModels() as $model) {
        $f = date('Y-m-d', strtotime($model->fecha));
        $fechas[$f] = $model->fecha;
    }
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
                    <td><?= Yii::$app->formatter->asDatetime($fechaRaw) ?></td>
                    <td>
                        <?= Html::a('<span class="glyphicon glyphicon-eye-open"></span> Ver detalles', ['inventory/detalle', 'fecha' => $fechaRaw], ['class' => 'btn btn-info btn-sm']) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
