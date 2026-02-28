<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel common\models\ConsumptionCenterSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Consumption Centers');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="consumption-center-index">


    <p>
        <?= Html::a(Yii::t('app', 'Create Consumption Center'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

//            'id',
           [
                    'attribute' => 'name',
                    'label' => 'Nombre',
                    'filter' => \yii\helpers\Html::activeTextInput($searchModel, 'name', [
                        'class' => 'form-control form-control-sm',
                        'style' => 'padding-right: 30px; background-image: url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxNiIgaGVpZ2h0PSIxNiIgdmlld0JveD0iMCAwIDE2IDE2Ij4KPHBhdGggZD0iTSAxMC41IDEgQyA4LjAyNzI3MjcgMSA2IDMuMDI3MjcyIDYgNS41IEMgNiA2LjU1NDE0NTkgNi40MjI3OTM2IDcuNDg2MTgxIDcuMDM3MTA5NCA4LjI1NTg1OTQgTCAyLjA0Njg3NSAxMy4yNDYwOTQgTCAyLjc1MzkwNjIgMTMuOTUzMTI1IEwgNy43NDQxNDA2IDguOTYyODkwNiBDIDguNTEzODE4NSA5LjU3NzIwNjQgOS40NDU4NTQxIDEwIDEwLjUgMTAgQyAxMi45NzI3MjcgMTAgMTUgNy45NzI3MjcgMTUgNS41IEMgMTUgMy4wMjcyNzMgMTIuOTcyNzMgMSAxMC41IDEgeiBNIDEwLjUgMiBDIDEyLjQyNzI3MyAyIDE0IDMuNTcyNzI3MyAxNCA1LjUgQyAxNCA3LjQyNzI3MyAxMi40MjcyNzMgOSAxMC41IDkgQyA4LjU3MjcyNyA5IDcgNy40MjcyNzMgNyA1LjUgQyA3IDMuNTcyNzI3MyA4LjU3MjcyNyAyIDEwLjUgMiB6Ij48L3BhdGg+Cjwvc3ZnPgo=); background-repeat: no-repeat; background-position: right 10px center;'
                    ]),
                ],
//            'business_id',

            [
                    'class' => 'yii\grid\ActionColumn',
                'template' => "{update} {delete}",
                'buttons' => [
                    'delete' => function ($url, $model, $key) {
                        return Html::a(
                            '<svg aria-hidden="true" style="display:inline-block;font-size:inherit;height:1em;overflow:visible;vertical-align:-.125em;width:.875em" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="currentColor" d="M32 464a48 48 0 0048 48h288a48 48 0 0048-48V128H32zm272-256a16 16 0 0132 0v224a16 16 0 01-32 0zm-96 0a16 16 0 0132 0v224a16 16 0 01-32 0zm-96 0a16 16 0 0132 0v224a16 16 0 01-32 0zM432 32H312l-9-19a24 24 0 00-22-13H167a24 24 0 00-22 13l-9 19H16A16 16 0 000 48v32a16 16 0 0016 16h416a16 16 0 0016-16V48a16 16 0 00-16-16z"></path></svg>',
                            '#',
                            [
                                'title' => Yii::t('yii', 'Delete'),
                                'aria-label' => Yii::t('yii', 'Delete'),
                                'class' => 'text-warning delete-cc-link',
                                'data-url' => $url,
                            ]
                        );
                    }
                ]
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>

<?php
$this->registerJs(<<<JS
$(document).on('click', '.delete-cc-link', function(e) {
    e.preventDefault();
    
    var deleteUrl = $(this).data('url');
    
    // Primera confirmación simple
    if (confirm('¿Está seguro de que desea eliminar este Centro de Consumo?')) {
        // Segunda confirmación con advertencia detallada
        if (confirm('⚠️ ADVERTENCIA: Este Centro de Consumo puede estar asociado a movimientos, inventarios y usuarios. Si lo elimina, afectará el historial y los datos relacionados. Esta acción no se puede deshacer.\\n\\n¿Realmente desea continuar con la eliminación?')) {
            // Crear un formulario y enviarlo
            var form = $('<form>', {
                'method': 'POST',
                'action': deleteUrl
            });
            
            var csrfParam = $('meta[name="csrf-param"]').attr('content');
            var csrfToken = $('meta[name="csrf-token"]').attr('content');
            
            form.append($('<input>', {
                'type': 'hidden',
                'name': csrfParam,
                'value': csrfToken
            }));
            
            $('body').append(form);
            form.submit();
        }
    }
    
    return false;
});
JS
, \yii\web\View::POS_READY);
?>
