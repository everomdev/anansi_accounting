<?php
use yii\grid\GridView;
use yii\helpers\Html;
$this->title = 'Comparación de Insumos';
$this->params['breadcrumbs'][] = ['label' => 'KPI', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="kpi-comparacion-insumos">
    <?php
        $nombre = Yii::$app->request->get('nombre', '');
        $fecha = Yii::$app->request->get('fecha');
        $categoria = Yii::$app->request->get('categoria', '');
        $categorias = isset($categorias) ? $categorias : [];
    ?>
    <form method="get" class="form-inline mb-3" action="">
        <input type="hidden" name="fecha" value="<?= Html::encode($fecha) ?>">
        <div class="form-group mr-2">
            <input type="text" name="nombre" value="<?= Html::encode($nombre) ?>" class="form-control" placeholder="Buscar insumo por nombre...">
        </div>
            <button type="submit" class="btn btn-primary mt-2 mb-2">Buscar</button>
        <?php if ($nombre): ?>
            <a href="?fecha=<?= Html::encode($fecha) ?>" class="btn btn-secondary ml-2">Limpiar</a>
        <?php endif; ?>
            <div class="form-group mr-2">
                <select name="categoria" class="form-control" onchange="this.form.submit();">
                    <option value="">Todas las categorías</option>
                    <?php foreach ($categorias as $catId => $catName): ?>
                        <option value="<?= Html::encode($catId) ?>" <?= $categoria == $catId ? 'selected' : '' ?>><?= Html::encode($catName) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
    </form>
    <div class="table-responsive">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => null,
            'tableOptions' => ['class' => 'table table-striped'],
            'layout' => "{items}\n<div class='d-flex justify-content-between align-items-center mt-3'><div>{pager}</div><div>{summary}</div></div>",
            'columns' => [
                [
                    'attribute' => 'nombre',
                    'label' => 'Insumo',
                    'contentOptions' => ['style' => 'text-align:center;'],
                    'headerOptions' => ['style' => 'text-align:center;'],
                ],
                [
                    'attribute' => 'categoria',
                    'label' => 'Categoría',
                    'contentOptions' => ['style' => 'text-align:center;'],
                    'headerOptions' => ['style' => 'text-align:center;'],
                ],
                [
                    'attribute' => 'unidad_compra',
                    'label' => 'Unidad<br>compra',
                    'contentOptions' => ['style' => 'text-align:center;'],
                    'headerOptions' => ['style' => 'text-align:center;'],
                ],
                [
                    'attribute' => 'existencia_almacen',
                    'label' => 'Existencia<br>almacén',
                    'format' => ['integer'],
                    'contentOptions' => ['style' => 'text-align:center;'],
                    'headerOptions' => ['style' => 'text-align:center;'],
                ],
                [
                    'attribute' => 'inventario_almacen',
                    'label' => 'Inventario<br>almacén',
                    'format' => ['integer'],
                    'contentOptions' => ['style' => 'text-align:center;'],
                    'headerOptions' => ['style' => 'text-align:center;'],
                ],
                [
                    'attribute' => 'compras_menos_consumo',
                    'label' => 'Compras - Consumo real',
                    'contentOptions' => ['style' => 'text-align:center;'],
                    'headerOptions' => ['style' => 'text-align:center;'],
                ],
            ],
        ]) ?>
    </div>
    <div class="mt-3">
    <?= Html::a('Volver al inventario', ['/inventory/detalle', 'fecha' => $fecha], ['class' => 'btn btn-secondary']) ?>
    </div>
</div>
