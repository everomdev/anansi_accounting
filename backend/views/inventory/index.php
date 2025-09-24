
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
    // Mostrar todas las fechas únicas completas (con hora) de toda la tabla
    $fechas = \common\models\Inventory::find()
        ->select('fecha')
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
