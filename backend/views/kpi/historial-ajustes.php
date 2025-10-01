<?php
use yii\grid\GridView;
use yii\helpers\Html;
use common\models\LogsInventario;

$this->title = 'Historial de Ajustes de Inventario';
$this->params['breadcrumbs'][] = ['label' => 'KPI', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="logs-inventario-index">
    <h2><?= Html::encode($this->title) ?></h2>
    
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> 
        Aquí puedes ver todos los ajustes realizados a las existencias del almacén, incluyendo quién los hizo y cuándo.
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'tableOptions' => ['class' => 'table table-striped'],
        'layout' => "{items}\n<div class='d-flex justify-content-between align-items-center mt-3'><div>{pager}</div><div>{summary}</div></div>",
        'columns' => [
            [
                'attribute' => 'fecha_ajuste',
                'label' => 'Fecha y Hora',
                'format' => ['datetime', 'php:d/m/Y H:i'],
                'headerOptions' => ['style' => 'text-align:center;'],
                'contentOptions' => ['style' => 'text-align:center;'],
                'filter' => \yii\jui\DatePicker::widget([
                    'model' => $searchModel,
                    'attribute' => 'fecha_ajuste',
                    'language' => 'es',
                    'dateFormat' => 'yyyy-MM-dd',
                    'options' => [
                        'placeholder' => 'Seleccionar fecha...',
                        'class' => 'form-control',
                    ]
                ]),
            ],
            [
                'attribute' => 'user_id',
                'label' => 'Usuario',
                'value' => function($model) {
                    return $model->user ? $model->user->username : 'N/A';
                },
                'headerOptions' => ['style' => 'text-align:center;'],
                'contentOptions' => ['style' => 'text-align:center;'],
                'filter' => Html::activeDropDownList(
                    $searchModel,
                    'user_id',
                    \yii\helpers\ArrayHelper::map(
                        array_merge(
                            // Usuarios asociados en user_business
                            \common\models\User::find()
                                ->leftJoin('user_business', 'user.id = user_business.user_id')
                                ->where(['user_business.business_id' => \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY)['id'] ?? null])
                                ->all(),
                            // Usuario principal (owner) del business
                            (function() {
                                $businessId = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY)['id'] ?? null;
                                $business = \common\models\Business::findOne($businessId);
                                if ($business && $business->user_id) {
                                    $ownerUser = \common\models\User::findOne($business->user_id);
                                    return $ownerUser ? [$ownerUser] : [];
                                }
                                return [];
                            })()
                        ),
                        'id',
                        'username'
                    ),
                    ['class' => 'form-control', 'prompt' => 'Todos los usuarios']
                ),
            ],
            [
                'attribute' => 'ingredient_stock_id',
                'label' => 'Insumo',
                'value' => function($model) {
                    if ($model->ingredientStock) {
                        $nombre = $model->ingredientStock->ingredient;
                        $marca = $model->ingredientStock->brand ?? '';
                        $presentacion = $model->ingredientStock->presentation ?? '';
                        $resultado = $nombre;
                        if($marca) $resultado .= ' ' . $marca;
                        if($presentacion) $resultado .= ' ' . $presentacion;
                        return $resultado;
                    }
                    return 'N/A';
                },
                'headerOptions' => ['style' => 'text-align:center;'],
                'contentOptions' => ['style' => 'text-align:center;'],
                'filter' => Html::activeTextInput($searchModel, 'insumo_nombre', [
                    'class' => 'form-control',
                    'placeholder' => 'Buscar insumo...'
                ]),
            ],
            [
                'attribute' => 'existencia_anterior',
                'label' => 'Existencia<br>Anterior',
                'encodeLabel' => false,
                'format' => ['decimal', 2],
                'headerOptions' => ['style' => 'text-align:center;'],
                'contentOptions' => ['style' => 'text-align:center; background-color: #ffebee;'],
            ],
            [
                'attribute' => 'existencia_nueva',
                'label' => 'Existencia<br>Nueva',
                'encodeLabel' => false,
                'format' => ['decimal', 2],
                'headerOptions' => ['style' => 'text-align:center;'],
                'contentOptions' => ['style' => 'text-align:center; background-color: #e8f5e8;'],
            ],
            [
                'label' => 'Diferencia',
                'value' => function($model) {
                    $diferencia = $model->existencia_nueva - $model->existencia_anterior;
                    return ($diferencia >= 0 ? '+' : '') . number_format($diferencia, 2);
                },
                'headerOptions' => ['style' => 'text-align:center;'],
                'contentOptions' => function($model) {
                    $diferencia = $model->existencia_nueva - $model->existencia_anterior;
                    $color = $diferencia > 0 ? '#e8f5e8' : ($diferencia < 0 ? '#ffebee' : '#f5f5f5');
                    return ['style' => "text-align:center; background-color: $color; font-weight: bold;"];
                },
            ],
            [
                'attribute' => 'motivo',
                'label' => 'Motivo',
                'value' => function($model) {
                    return $model->motivo ?: 'Sin motivo especificado';
                },
                'headerOptions' => ['style' => 'text-align:center;'],
                'contentOptions' => ['style' => 'text-align:left;'],
                'filter' => Html::activeTextInput($searchModel, 'motivo', [
                    'class' => 'form-control',
                    'placeholder' => 'Buscar por motivo...'
                ]),
            ],
        ],
    ]) ?>
    
    <div class="mt-3">
        <?= Html::a('Volver a KPI', ['/inventory/index'], ['class' => 'btn btn-secondary']) ?>
    </div>
</div>
