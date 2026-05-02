<?php
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $ingredientProvider yii\data\ArrayDataProvider */
/* @var $recipeProvider yii\data\ArrayDataProvider */
/* @var $subrecipeProvider yii\data\ArrayDataProvider */
/* @var $counts array */

$this->title = 'Pendientes Globales';
$this->params['breadcrumbs'][] = $this->title;

$fieldLabels = [
    // Insumos
    'ingredient'        => 'Insumo',
    'category_id'       => 'Familia',
    'um'                => 'Unidad de Compra',
    'brand'             => 'Marca',
    'presentation'      => 'Presentación',
    'portion_um'        => 'Unidades de uso',
    'portions_per_unit' => 'Equivalencia',
    'price'             => 'Precio de compra',
    'yield'             => 'Rendimiento',
    'providers'         => 'Proveedores',
    'min_stock'         => 'Stock Mínimo',
    'max_stock'         => 'Stock Máximo',
    'key'               => 'Clave',
    'adjustedPrice'     => 'Precio ajustado',
    'observations'      => 'Observaciones',
    // Recetas / subrecetas
    'title'               => 'Nombre',
    'type'                => 'Tipo',
    'type_of_recipe'      => 'Tipo de receta',
    'time_of_preparation' => 'Tiempo de preparación',
    'yield_um'            => 'Unidad de rendimiento',
    'convoy_id'           => 'Convoy',
    'portions'            => 'Porciones',
    'lifetime'            => 'Duración',
    'price'               => 'Precio',
    'observation'         => 'Observaciones',
    'steps'               => 'Pasos',
    'allergies'           => 'Alergias',
    'flowchart'           => 'Diagrama',
    'equipment'           => 'Equipo',
    // Ingredientes de receta
    'quantity'          => 'Cantidad',
    'cost_percentage'   => 'Porcentaje de costo',
    'exclude_from_cost' => 'Exclusión del costeo',
];

$css = <<<CSS
.gp-wrap { padding: 0 4px; }

/* ── Summary cards ── */
.gp-summary {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 14px;
    margin-bottom: 28px;
}
@media (max-width: 700px) { .gp-summary { grid-template-columns: 1fr 1fr; } }

.gp-card {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    padding: 18px 20px 16px;
    display: flex; align-items: center; gap: 14px;
    box-shadow: 0 1px 6px rgba(0,0,0,.04);
    cursor: pointer;
    transition: box-shadow .18s, border-color .18s, transform .15s;
}
.gp-card:hover { box-shadow: 0 4px 18px rgba(0,0,0,.09); transform: translateY(-2px); }
.gp-card.active-card { border-color: var(--gp-accent); box-shadow: 0 0 0 2px var(--gp-accent-soft); }

.gp-card[data-tab="ingredient"] { --gp-accent:#f59e0b; --gp-accent-soft:rgba(245,158,11,.12); }
.gp-card[data-tab="recipe"]     { --gp-accent:#3b82f6; --gp-accent-soft:rgba(59,130,246,.12); }
.gp-card[data-tab="subrecipe"]  { --gp-accent:#8b5cf6; --gp-accent-soft:rgba(139,92,246,.12); }

.gp-card-icon {
    width:46px; height:46px; border-radius:11px;
    display:flex; align-items:center; justify-content:center;
    font-size:1.35rem; flex-shrink:0;
}
.gp-icon-ingredient { background:#fff8e1; color:#f59e0b; }
.gp-icon-recipe     { background:#e8f4fd; color:#3b82f6; }
.gp-icon-subrecipe  { background:#f3e8ff; color:#8b5cf6; }

.gp-card-count { font-size:1.7rem; font-weight:800; line-height:1; color:#1a1d23; letter-spacing:-.02em; }
.gp-card-label { font-size:0.74rem; font-weight:600; text-transform:uppercase; letter-spacing:.07em; color:#868e96; margin-top:3px; }

/* ── Tabs ── */
.gp-tabs {
    display:flex; gap:4px;
    border-bottom:2px solid #e9ecef;
    margin-bottom:0;
}
.gp-tab-btn {
    display:flex; align-items:center; gap:7px;
    padding:10px 18px;
    border:none; border-bottom:2px solid transparent;
    background:transparent;
    font-size:0.84rem; font-weight:600; color:#868e96;
    margin-bottom:-2px; cursor:pointer;
    border-radius:8px 8px 0 0;
    transition:color .15s, border-color .15s, background .15s;
    white-space:nowrap;
}
.gp-tab-btn:hover { background:#f8f9fb; color:#495057; }
.gp-tab-btn.active { color:#1a1d23; border-bottom-color: var(--gp-tab-color,#3b82f6); }
.gp-tab-btn[data-tab="ingredient"] { --gp-tab-color:#f59e0b; }
.gp-tab-btn[data-tab="recipe"]     { --gp-tab-color:#3b82f6; }
.gp-tab-btn[data-tab="subrecipe"]  { --gp-tab-color:#8b5cf6; }

.gp-tab-badge {
    display:inline-flex; align-items:center; justify-content:center;
    min-width:20px; height:20px; border-radius:10px;
    font-size:0.68rem; font-weight:700; padding:0 6px;
    background:#f1f3f5; color:#495057;
}
.gp-tab-btn.active .gp-tab-badge { background:var(--gp-tab-color,#3b82f6); color:#fff; }

/* ── Table wrap ── */
.gp-table-wrap {
    background:#fff;
    border:1px solid #e9ecef; border-top:none;
    border-radius:0 0 12px 12px;
    overflow:hidden;
    box-shadow:0 1px 6px rgba(0,0,0,.04);
}
.gp-panel { display:none; }
.gp-panel.active { display:block; }

/* GridView resets */
.gp-table-wrap .grid-view { overflow-x:auto; }
.gp-table-wrap table.table { margin:0; font-size:0.875rem; }
.gp-table-wrap table.table thead th {
    background:#f8f9fb; color:#495057;
    font-size:0.72rem; font-weight:700;
    text-transform:uppercase; letter-spacing:.06em;
    border-bottom:1px solid #dee2e6;
    padding:13px 16px; white-space:nowrap; vertical-align:middle;
}
.gp-table-wrap table.table tbody tr { transition:background .13s; }
.gp-table-wrap table.table tbody tr:hover { background:#f8f9fb; }
.gp-table-wrap table.table tbody td {
    padding:13px 16px; vertical-align:top;
    border-bottom:1px solid #f1f3f5; color:#343a40;
}
.gp-table-wrap table.table tbody tr:last-child td { border-bottom:none; }

/* ── Item name ── */
.item-name { font-weight:600; color:#1a1d23; }

/* ── Own-fields badges (receta/insumo) ── */
.pending-badge {
    display:inline-flex; align-items:center; gap:4px;
    padding:3px 9px; border-radius:20px;
    font-size:0.73rem; font-weight:600;
    background:#fff3e0; color:#bf360c; border:1px solid #ffccbc;
    margin:2px 3px 2px 0; white-space:nowrap;
}

/* ── Ingredient sub-table ── */
.ing-subtable-wrap {
    margin-top:10px;
}
.ing-subtable-label {
    font-size:0.7rem; font-weight:700; text-transform:uppercase;
    letter-spacing:.07em; color:#868e96; margin-bottom:5px;
}
.ing-subtable {
    width:100%; border-collapse:collapse;
    font-size:0.8rem;
}
.ing-subtable thead th {
    background:#f1f3f5; color:#495057;
    font-size:0.68rem; font-weight:700;
    text-transform:uppercase; letter-spacing:.05em;
    padding:6px 10px; border:1px solid #e9ecef;
    white-space:nowrap;
}
.ing-subtable tbody td {
    padding:6px 10px; border:1px solid #e9ecef;
    vertical-align:middle; color:#343a40;
}
.ing-subtable tbody tr:nth-child(even) { background:#fafafa; }
.ing-type-badge {
    display:inline-flex; align-items:center; gap:4px;
    padding:2px 7px; border-radius:12px;
    font-size:0.68rem; font-weight:700;
}
.ing-type-insumo   { background:#e8f4fd; color:#1565c0; border:1px solid #bbdefb; }
.ing-type-subreceta{ background:#f3e8ff; color:#6d28d9; border:1px solid #ddd6fe; }
.ing-badge {
    display:inline-flex; align-items:center;
    padding:2px 7px; border-radius:12px;
    font-size:0.68rem; font-weight:600;
    background:#fef9c3; color:#854d0e; border:1px solid #fef08a;
    margin:1px 2px 1px 0; white-space:nowrap;
}

/* ── Empty state ── */
.gp-empty {
    text-align:center; padding:52px 20px; color:#868e96;
}
.gp-empty i { font-size:2.6rem; display:block; margin-bottom:12px; opacity:.35; }
.gp-empty p { font-size:0.88rem; margin:0; }

/* ── Actions ── */
.action-btns { display:flex; gap:6px; align-items:center; }
.action-btns a {
    display:inline-flex; align-items:center; justify-content:center;
    width:32px; height:32px; border-radius:7px;
    font-size:0.88rem; text-decoration:none;
    transition:background .15s, transform .12s;
}
.action-btns .btn-edit   { background:#e8f4fd; color:#1565c0; border:1px solid #bbdefb; }
.action-btns .btn-edit:hover { background:#bbdefb; transform:scale(1.08); color:#0d47a1; }

/* pager / summary */
.gp-table-wrap .pagination {
    padding:12px 16px; margin:0;
    justify-content:flex-end; border-top:1px solid #f1f3f5;
}
.gp-table-wrap .pagination .page-link {
    border-radius:6px; border:1px solid #dee2e6;
    color:#495057; padding:5px 11px; font-size:0.82rem; margin:0 2px;
}
.gp-table-wrap .pagination .page-item.active .page-link {
    background:#2563eb; border-color:#2563eb; color:#fff;
}
.gp-table-wrap .summary {
    padding:10px 16px; color:#868e96;
    font-size:0.78rem; border-top:1px solid #f1f3f5;
}
CSS;
$this->registerCss($css);

// ── Helpers ────────────────────────────────────────────────────────────────

// Render badge chips for a list of field keys
$renderBadges = function (array $fields) use ($fieldLabels): string {
    if (empty($fields)) return '';
    return implode('', array_map(function ($f) use ($fieldLabels) {
        $label = $fieldLabels[$f] ?? ucfirst(str_replace('_', ' ', $f));
        return "<span class='pending-badge'>" . Html::encode($label) . "</span>";
    }, $fields));
};

// Render the nested ingredient sub-table for a recipe/subrecipe row
$renderIngredientRows = function (array $rows) use ($fieldLabels): string {
    if (empty($rows)) return '';

    $html  = "<div class='ing-subtable-wrap'>";
    $html .= "<div class='ing-subtable-label'><i class='fas fa-list-ul me-1'></i>Ingredientes / Subrecetas con pendientes</div>";
    $html .= "<table class='ing-subtable'><thead><tr>";
    $html .= "<th>Tipo</th><th>Nombre</th><th>Campos pendientes</th>";
    $html .= "</tr></thead><tbody>";

    foreach ($rows as $r) {
        $typeBadge = $r['is_recipe']
            ? "<span class='ing-type-badge ing-type-subreceta'><i class='fas fa-layer-group'></i> Subreceta</span>"
            : "<span class='ing-type-badge ing-type-insumo'><i class='fas fa-box'></i> Insumo</span>";

        $fieldBadges = implode('', array_map(function ($f) use ($fieldLabels) {
            $label = $fieldLabels[$f] ?? ucfirst(str_replace('_', ' ', $f));
            return "<span class='ing-badge'>" . Html::encode($label) . "</span>";
        }, $r['pending_fields']));

        $html .= "<tr>";
        $html .= "<td>{$typeBadge}</td>";
        $html .= "<td><strong>" . Html::encode($r['item_name']) . "</strong></td>";
        $html .= "<td>{$fieldBadges}</td>";
        $html .= "</tr>";
    }

    $html .= "</tbody></table></div>";
    return $html;
};

// Build a GridView for recipes or subrecipes (same structure)
$buildRecipeGrid = function (
    \yii\data\ArrayDataProvider $provider,
    string $editRoute
) use ($renderBadges, $renderIngredientRows): string {
    return GridView::widget([
        'dataProvider' => $provider,
        'tableOptions' => ['class' => 'table'],
        'columns'      => [
            [
                'label'  => 'Nombre',
                'format' => 'raw',
                'value'  => fn($m) => "<div class='item-name'>" . Html::encode($m['name'] ?? '—') . "</div>",
            ],
            [
                'label'  => 'Campos propios pendientes',
                'format' => 'raw',
                'value'  => function ($m) use ($renderBadges) {
                    $badges = $renderBadges($m['pending_fields'] ?? []);
                    return $badges ?: "<span class='text-muted' style='font-size:.8rem'>—</span>";
                },
            ],
            [
                'label'  => 'Ingredientes / Subrecetas pendientes',
                'format' => 'raw',
                'value'  => function ($m) use ($renderIngredientRows) {
                    if (empty($m['ingredient_rows'])) {
                        return "<span class='text-muted' style='font-size:.8rem'>—</span>";
                    }
                    return $renderIngredientRows($m['ingredient_rows']);
                },
            ],
            [
                'label'  => 'Acciones',
                'format' => 'raw',
                'value'  => function ($m) use ($editRoute) {
                    $edit = Html::a(
                        '<i class="fas fa-pencil-alt"></i>',
                        [$editRoute, 'id' => $m['id']],
                        ['class' => 'btn-edit', 'title' => 'Editar']
                    );
                    return "<div class='action-btns'>{$edit}</div>";
                },
            ],
        ],
    ]);
};
?>

<div class="gp-wrap">

    <!-- Summary cards -->
    <div class="gp-summary">
        <div class="gp-card" data-tab="ingredient">
            <div class="gp-card-icon gp-icon-ingredient"><i class="fas fa-boxes"></i></div>
            <div class="gp-card-body">
                <div class="gp-card-count"><?= $counts['ingredient'] ?></div>
                <div class="gp-card-label">Insumos</div>
            </div>
        </div>
        <div class="gp-card" data-tab="subrecipe">
            <div class="gp-card-icon gp-icon-subrecipe"><i class="fas fa-layer-group"></i></div>
            <div class="gp-card-body">
                <div class="gp-card-count"><?= $counts['subrecipe'] ?></div>
                <div class="gp-card-label">Subrecetas</div>
            </div>
        </div>
        <div class="gp-card" data-tab="recipe">
            <div class="gp-card-icon gp-icon-recipe"><i class="fas fa-utensils"></i></div>
            <div class="gp-card-body">
                <div class="gp-card-count"><?= $counts['recipe'] ?></div>
                <div class="gp-card-label">Recetas</div>
            </div>
        </div>
    </div>

    <!-- Tab nav -->
    <div class="gp-tabs">
        <button class="gp-tab-btn active" data-tab="ingredient">
            <i class="fas fa-boxes"></i> Insumos
            <span class="gp-tab-badge"><?= $counts['ingredient'] ?></span>
        </button>
        <button class="gp-tab-btn" data-tab="subrecipe">
            <i class="fas fa-layer-group"></i> Subrecetas
            <span class="gp-tab-badge"><?= $counts['subrecipe'] ?></span>
        </button>
        <button class="gp-tab-btn" data-tab="recipe">
            <i class="fas fa-utensils"></i> Recetas
            <span class="gp-tab-badge"><?= $counts['recipe'] ?></span>
        </button>
    </div>

    <!-- Panels -->
    <div class="gp-table-wrap">

        <!-- ── Insumos ── -->
        <div class="gp-panel active" id="panel-ingredient">
            <?php if ($counts['ingredient'] === 0): ?>
                <div class="gp-empty">
                    <i class="fas fa-check-circle"></i>
                    <p>No hay insumos con campos pendientes.</p>
                </div>
            <?php else: ?>
                <?= GridView::widget([
                    'dataProvider' => $ingredientProvider,
                    'tableOptions' => ['class' => 'table'],
                    'columns'      => [
                        [
                            'label'  => 'Insumo',
                            'format' => 'raw',
                            'value'  => fn($m) => "<div class='item-name'>" . Html::encode($m['name'] ?? '—') . "</div>",
                        ],
                        [
                            'label'  => 'Campos Pendientes',
                            'format' => 'raw',
                            'value'  => fn($m) => $renderBadges($m['pending_fields'] ?? []),
                        ],
                        [
                            'label'  => 'Acciones',
                            'format' => 'raw',
                            'value'  => function ($m) {
                                $edit = Html::a('<i class="fas fa-pencil-alt"></i>',
                                    ['ingredient-stock/update', 'id' => $m['id']],
                                    ['class' => 'btn-edit', 'title' => 'Editar insumo']);
                                return "<div class='action-btns'>{$edit}</div>";
                            },
                        ],
                    ],
                ]) ?>
            <?php endif; ?>
        </div>

        <!-- ── Subrecetas ── -->
        <div class="gp-panel" id="panel-subrecipe">
            <?php if ($counts['subrecipe'] === 0): ?>
                <div class="gp-empty">
                    <i class="fas fa-check-circle"></i>
                    <p>No hay subrecetas con campos pendientes.</p>
                </div>
            <?php else: ?>
                <?= $buildRecipeGrid($subrecipeProvider, 'standard-recipe/update') ?>
            <?php endif; ?>
        </div>

        <!-- ── Recetas ── -->
        <div class="gp-panel" id="panel-recipe">
            <?php if ($counts['recipe'] === 0): ?>
                <div class="gp-empty">
                    <i class="fas fa-check-circle"></i>
                    <p>No hay recetas con campos pendientes.</p>
                </div>
            <?php else: ?>
                <?= $buildRecipeGrid($recipeProvider, 'standard-recipe/update') ?>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php
$js = <<<JS
(function () {
    const tabs  = document.querySelectorAll('.gp-tab-btn');
    const cards = document.querySelectorAll('.gp-card');

    function activate(tab) {
        tabs.forEach(b  => b.classList.remove('active'));
        cards.forEach(c => c.classList.remove('active-card'));
        document.querySelectorAll('.gp-panel').forEach(p => p.classList.remove('active'));

        document.querySelectorAll('[data-tab="' + tab + '"]').forEach(el => {
            el.classList.add(el.classList.contains('gp-panel') ? 'active' : (el.tagName === 'BUTTON' ? 'active' : 'active-card'));
        });
        // target panel directly
        const panel = document.getElementById('panel-' + tab);
        if (panel) panel.classList.add('active');
    }

    tabs.forEach(btn   => btn.addEventListener('click',  () => activate(btn.dataset.tab)));
    cards.forEach(card => card.addEventListener('click', () => activate(card.dataset.tab)));

    // Auto-select first tab with items
    const order = ['ingredient', 'subrecipe', 'recipe'];
    const first = order.find(t => {
        const badge = document.querySelector('.gp-tab-btn[data-tab="' + t + '"] .gp-tab-badge');
        return badge && parseInt(badge.textContent) > 0;
    });
    if (first) activate(first);
})();
JS;
$this->registerJs($js);
?>