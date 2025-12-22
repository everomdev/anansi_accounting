<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel common\models\ConvoySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->registerJsFile(Yii::getAlias("@web/js/convoy/index.js"), [
    'depends' => [\yii\web\YiiAsset::class]
]);
$this->title = Yii::t('app', 'Convoys');
$this->params['breadcrumbs'][] = $this->title;
$businessData = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);
$business = \common\models\Business::findOne(['id' => $businessData['id']]);
$totalSales = array_sum(\yii\helpers\ArrayHelper::getColumn($dataProvider->models, 'amount'));
?>
<div class="convoy-index">

    <p>
        <?= Html::a(Yii::t('app', 'Create Convoy'), ['create'], ['class' => 'btn btn-success']) ?>
        <?= Html::a(Yii::t('app', 'Descargar Plantilla'), ['export-convoy-template'], ['class' => 'btn btn-success ms-2']) ?>

        <!-- Botón para importar -->
        <button type="button" class="btn btn-success ms-2" data-bs-toggle="modal" data-bs-target="#importModal">
             Importar Convoys
        </button>
    </p>

    <!-- Modal para importar -->
    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Importar Convoys desde Excel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php $form = \yii\widgets\ActiveForm::begin([
                        'action' => ['import-convoy-excel'],
                        'options' => ['enctype' => 'multipart/form-data']
                    ]); ?>

                    <div class="mb-3">
                        <label for="convoy_file" class="form-label">Seleccionar archivo Excel</label>
                        <input type="file" class="form-control" id="convoy_file" name="convoy_file" accept=".xlsx,.xls" required>
                        <div class="form-text">
                            Seleccione el archivo Excel con la plantilla de convoys completada.
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <?= Html::submitButton('<i class="fas fa-upload"></i> Importar', ['class' => 'btn btn-success']) ?>
                    </div>

                    <?php \yii\widgets\ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>    <?= GridView::widget([
        'dataProvider' => $dataProvider,
//        'filterModel' => $searchModel,
        'tableOptions' => ['class' => 'table table-striped'],
        'columns' => [
            [
                'class' => 'yii\grid\SerialColumn',
                'headerOptions' => ['style' => 'text-align: center;'],
                'contentOptions' => ['style' => 'text-align: center !important;'],
            ],
            [
                'attribute' => 'name',
                'headerOptions' => ['style' => 'text-align: center;'],
                'contentOptions' => ['style' => 'text-align: center !important;'],
            ],
            [
                'label' => "Platillos",
                'value' => function ($data) {
                    return $data->plates;
                },
                'headerOptions' => ['style' => 'text-align: center;'],
                'contentOptions' => ['style' => 'text-align: center !important;'],
            ],
            [
                'attribute' => 'amount',
                'label' => "Monto",
                'value' => function($model) {
                    return formatPrice($model->amount);
                },
                'headerOptions' => ['style' => 'text-align: center;'],
                'contentOptions' => ['style' => 'text-align: center !important;'],
            ],
            [
                'attribute' => 'totalAmount',
                'label' => "Costo",
                'value' => function($model) {
                    return formatCost($model->totalAmount);
                },
                'headerOptions' => ['style' => 'text-align: center;'],
                'contentOptions' => ['style' => 'text-align: center !important;'],
            ],
            [
                'attribute' => 'observations',
                'value' => function ($data) {
                    return empty($data->observations) ? "Sin observaciones" : $data->observations;
                },
                'headerOptions' => ['style' => 'text-align: center;'],
                'contentOptions' => ['style' => 'text-align: center !important;'],
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => "{update} {delete}"
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>

<?php
$this->registerJs("
    $('#importModal form').on('submit', function(e) {
        var submitBtn = $(this).find('button[type=\"submit\"]');
        submitBtn.prop('disabled', true);
        submitBtn.html('<i class=\"fas fa-spinner fa-spin\"></i> Cargando...');
    });
");
?>
