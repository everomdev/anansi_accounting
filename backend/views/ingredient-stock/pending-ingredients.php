<?php
use yii\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Pendientes de Insumos';
$this->params['breadcrumbs'][] = $this->title;

$css = <<<CSS
.pending-ingredients-index {
    padding: 0 4px;
}



/* ── Table card ── */
.pi-table-card {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 1px 6px rgba(0,0,0,.04);
}

/* Yii2 GridView overrides */
.pi-table-card .grid-view {
    overflow-x: auto;
}
.pi-table-card table.table {
    margin: 0;
    font-size: 0.875rem;
}
.pi-table-card table.table thead th {
    background: #f8f9fb;
    color: #495057;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    border-bottom: 1px solid #dee2e6;
    padding: 13px 16px;
    white-space: nowrap;
    vertical-align: middle;
}
.pi-table-card table.table tbody tr {
    transition: background .13s;
}
.pi-table-card table.table tbody tr:hover {
    background: #f8f9fb;
}
.pi-table-card table.table tbody td {
    padding: 13px 16px;
    vertical-align: middle;
    border-bottom: 1px solid #f1f3f5;
    color: #343a40;
}
.pi-table-card table.table tbody tr:last-child td {
    border-bottom: none;
}

/* ingredient name cell */
.ingredient-name {
    font-weight: 600;
    color: #1a1d23;
    display: flex;
    align-items: center;
    gap: 8px;
}
.ingredient-avatar {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    font-size: 0.75rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    text-transform: uppercase;
}

/* pending fields badges */
.pending-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 9px;
    border-radius: 20px;
    font-size: 0.73rem;
    font-weight: 600;
    background: #fff3e0;
    color: #bf360c;
    border: 1px solid #ffccbc;
    margin: 2px 3px 2px 0;
    white-space: nowrap;
}
.pending-badge i { font-size: 0.65rem; }
.no-pending {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    color: #2e7d32;
    font-size: 0.82rem;
    font-weight: 600;
}
.no-pending i { font-size: 0.85rem; }

/* date cell */
.date-cell {
    white-space: nowrap;
    color: #495057;
}
.date-cell .date-main {
    font-weight: 600;
    color: #343a40;
}
.date-cell .date-time {
    font-size: 0.75rem;
    color: #868e96;
}

/* actions */
.action-btns {
    display: flex;
    gap: 6px;
    align-items: center;
}
.action-btns a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 7px;
    font-size: 0.88rem;
    text-decoration: none;
    transition: background .15s, transform .12s;
}
.action-btns .btn-edit {
    background: #e8f4fd;
    color: #1565c0;
    border: 1px solid #bbdefb;
}
.action-btns .btn-edit:hover {
    background: #bbdefb;
    transform: scale(1.08);
    color: #0d47a1;
}
/* pager override */
.pi-table-card .pagination {
    padding: 12px 16px;
    margin: 0;
    justify-content: flex-end;
    border-top: 1px solid #f1f3f5;
}
.pi-table-card .pagination .page-link {
    border-radius: 6px;
    border: 1px solid #dee2e6;
    color: #495057;
    padding: 5px 11px;
    font-size: 0.82rem;
    margin: 0 2px;
}
.pi-table-card .pagination .page-item.active .page-link {
    background: #2563eb;
    border-color: #2563eb;
    color: #fff;
}

/* summary */
.pi-table-card .summary {
    padding: 10px 16px;
    color: #868e96;
    font-size: 0.78rem;
    border-top: 1px solid #f1f3f5;
}
CSS;

$this->registerCss($css);

$fieldLabels = [
    'ingredient'       => 'Insumo',
    'category_id'      => 'Familia',
    'um'               => 'Unidad de Compra',
    'brand'            => 'Marca',
    'presentation'     => 'Presentación',
    'portion_um'       => 'Unidades de uso',
    'portions_per_unit'=> 'Equivalencia',
    'price'            => 'Precio de compra',
    'yield'            => 'Rendimiento',
    'providers'        => 'Proveedores',
    'min_stock'        => 'Stock Mínimo',
    'max_stock'        => 'Stock Máximo',
    'key'              => 'Clave',
    'adjustedPrice'    => 'Precio ajustado',
];
?>

<div class="pending-ingredients-index">

    <!-- Table -->
    <div class="pi-table-card">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'tableOptions' => ['class' => 'table'],
            'columns' => [
                [
                    'label'  => 'Insumo',
                    'format' => 'raw',
                    'value'  => function($model) {
                        $name     = Html::encode($model->ingredient ?? '—');
                        return "<div class='ingredient-name'>
                                    {$name}
                                </div>";
                    },
                ],
                [
                    'label'  => 'Campos Pendientes',
                    'format' => 'raw',
                    'value'  => function($model) use ($fieldLabels) {
                        if (empty($model->pending_fields)) {
                            return "<span class='no-pending'><i class='bi bi-check-circle-fill'></i> Completo</span>";
                        }
                        $badges = array_map(function($f) use ($fieldLabels) {
                            $label = $fieldLabels[$f] ?? ucfirst($f);
                            return "<span class='pending-badge'><i class='bi bi-dot'></i>" . Html::encode($label) . "</span>";
                        }, $model->pending_fields);
                        return implode('', $badges);
                    },
                ],
                // [
                //     'label'  => 'Última actualización',
                //     'format' => 'raw',
                //     'value'  => function($model) {
                //         if (!$model->pending_updated_at) {
                //             return "<span class='text-muted' style='font-size:.8rem'>—</span>";
                //         }
                //         $ts   = is_numeric($model->pending_updated_at)
                //                     ? $model->pending_updated_at
                //                     : strtotime($model->pending_updated_at);
                //         $date = date('d/m/Y', $ts);
                //         $time = date('H:i', $ts);
                //         return "<div class='date-cell'>
                //                     <div class='date-main'><i class='bi bi-calendar3 me-1 text-muted'></i>{$date}</div>
                //                     <div class='date-time'><i class='bi bi-clock me-1'></i>{$time}</div>
                //                 </div>";
                //     },
                // ],
                [
                    'label'  => 'Acciones',
                    'format' => 'raw',
                    'value'  => function($model) {
                        $edit = Html::a(
                            '<i class="fas fa-pencil-alt"></i>',
                            ['ingredient-stock/update', 'id' => $model->id],
                            [
                                'class' => 'btn-edit update-ingredient-link',
                                'title' => 'Editar',
                                'data-update-url' => yii\helpers\Url::to(['ingredient-stock/update', 'id' => $model->id]),
                                'data-recipes' => method_exists($model, 'getRecipes') ? $model->getRecipes()->count() : 0,
                                'data-subrecipes' => method_exists($model, 'getSubRecipes') ? $model->getSubRecipes()->count() : 0,
                            ]
                        );
                        return "<div class='action-btns'>{$edit}</div>";
                    },
                ],
            ],
        ]) ?>
    </div>

</div>