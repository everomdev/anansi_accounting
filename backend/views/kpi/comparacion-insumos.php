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
                    <option value="">Todas las familias</option>
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
                    'label' => 'Familias',
                    'contentOptions' => ['style' => 'text-align:center;'],
                    'headerOptions' => ['style' => 'text-align:center;'],
                ],
                [
                    'attribute' => 'unidad_compra',
                    'label' => 'Unidad<br>compra',
                    'encodeLabel' => false,
                    'contentOptions' => ['style' => 'text-align:center;'],
                    'headerOptions' => ['style' => 'text-align:center;'],
                ],
                [
                    'attribute' => 'existencia_almacen',
                    'label' => 'Existencia<br>almacén',
                    'encodeLabel' => false,
                    'format' => ['integer'],
                    'contentOptions' => ['style' => 'text-align:center;'],
                    'headerOptions' => ['style' => 'text-align:center;'],
                ],
                [
                    'attribute' => 'inventario_almacen',
                    'label' => 'Inventario<br>almacén',
                    'encodeLabel' => false,
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
                [
                    'class' => 'yii\grid\ActionColumn',
                    'header' => 'Ajustar',
                    'headerOptions' => ['style' => 'text-align:center; width: 100px;'],
                    'contentOptions' => ['style' => 'text-align:center;'],
                    'template' => '{ajustar}',
                    'buttons' => [
                        'ajustar' => function ($url, $model, $key) {
                            if ($model['existencia_almacen'] != $model['inventario_almacen']) {
                                return Html::button('<i class="fas fa-adjust"></i> Ajustar', [
                                    'class' => 'btn btn-warning btn-sm',
                                    'title' => 'Ajustar existencia del almacén',
                                    'onclick' => "ajustarExistencia({$model['ingredient_stock_id']}, '{$model['nombre']}', {$model['existencia_almacen']}, {$model['inventario_almacen']})"
                                ]);
                            }
                            return '';
                        }
                    ]
                ],
            ],
        ]) ?>
    </div>
    <div class="mt-3">
    <button type="button" class="btn btn-success mr-2" onclick="ajustarTodos()">
        <i class="fas fa-sync-alt"></i> Ajustar Todos al Inventario Físico
    </button>
    <?= Html::a('<i class="fas fa-history"></i> Ver Historial de Ajustes', ['/kpi/historial-ajustes'], ['class' => 'btn btn-secondary mr-2']) ?>
    <?= Html::a('Volver al inventario', ['/inventory/detalle', 'fecha' => $fecha], ['class' => 'btn btn-secondary']) ?>
    </div>
</div>

<!-- Modal para ajustar existencia -->
<div class="modal fade" id="ajustarModal" tabindex="-1" role="dialog" aria-labelledby="ajustarModalLabel" aria-hidden="true" data-backdrop="true" data-keyboard="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ajustarModalLabel">Ajustar Existencia de Almacén</h5>
                <button type="button" class="close" onclick="cerrarModal()" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="ajustarForm">
                <div class="modal-body">
                    <input type="hidden" id="ingredientStockId" name="ingredient_stock_id">
                    <div class="form-group">
                        <label>Insumo:</label>
                        <p id="nombreInsumo" class="font-weight-bold"></p>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Existencia Actual:</label>
                                <input type="number" id="existenciaActual" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Inventario Físico:</label>
                                <input type="number" id="inventarioFisico" class="form-control" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Nueva Existencia:</label>
                        <input type="number" id="nuevaExistencia" name="nueva_existencia" class="form-control" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>Motivo (opcional):</label>
                        <textarea id="motivo" name="motivo" class="form-control" rows="3" placeholder="Razón del ajuste..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="cerrarModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Ajustar Existencia</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
function ajustarExistencia(ingredientStockId, nombre, existenciaActual, inventarioFisico) {
    $('#ingredientStockId').val(ingredientStockId);
    $('#nombreInsumo').text(nombre);
    $('#existenciaActual').val(existenciaActual);
    $('#inventarioFisico').val(inventarioFisico);
    $('#nuevaExistencia').val(inventarioFisico); // Por defecto, ajustar al inventario físico
    $('#motivo').val('');
    $('#ajustarModal').modal('show');
}

function cerrarModal() {
    $('#ajustarModal').modal('hide');
}

function ajustarTodos() {
    if (!confirm('¿Estás seguro de que quieres ajustar TODOS los insumos con diferencias al valor del inventario físico?')) {
        return;
    }
    
    // Recopilar todos los datos de la tabla
    var ajustes = [];
    $('table tbody tr').each(function() {
        var $row = $(this);
        var existenciaAlmacen = parseFloat($row.find('td:eq(3)').text()) || 0;
        var inventarioFisico = parseFloat($row.find('td:eq(4)').text()) || 0;
        
        // Solo si hay diferencia
        if (existenciaAlmacen !== inventarioFisico) {
            var ajusteBtn = $row.find('button[onclick*="ajustarExistencia"]');
            if (ajusteBtn.length > 0) {
                var onclick = ajusteBtn.attr('onclick');
                var matches = onclick.match(/ajustarExistencia\((\d+),\s*'([^']+)',\s*([^,]+),\s*([^)]+)\)/);
                if (matches) {
                    ajustes.push({
                        ingredient_stock_id: matches[1],
                        nombre: matches[2],
                        existencia_anterior: matches[3],
                        nueva_existencia: matches[4],
                        motivo: 'Ajuste masivo al inventario físico'
                    });
                }
            }
        }
    });
    
    if (ajustes.length === 0) {
        alert('No hay insumos con diferencias para ajustar.');
        return;
    }
    
    // Mostrar progreso
    var progressHtml = '<div class="alert alert-info" id="progressAlert">' +
        '<i class="fas fa-spinner fa-spin"></i> Ajustando ' + ajustes.length + ' insumos...' +
        '</div>';
    $('.mt-3').prepend(progressHtml);
    
    // Enviar ajustes por lote
    $.ajax({
        url: '<?= \yii\helpers\Url::to(['/kpi/ajustar-existencia-masivo']) ?>',
        type: 'POST',
        data: {
            ajustes: ajustes
        },
        success: function(response) {
            $('#progressAlert').remove();
            if (response.success) {
                alert('Se ajustaron ' + response.ajustados + ' insumos correctamente.');
                location.reload();
            } else {
                alert('Error: ' + (response.message || 'No se pudieron ajustar los insumos'));
            }
        },
        error: function() {
            $('#progressAlert').remove();
            alert('Error al conectar con el servidor');
        }
    });
}

$(document).ready(function() {
    // Event listener para cerrar modal con X
    $('.close').on('click', function() {
        cerrarModal();
    });
    
    // Event listener para botón cancelar
    $('button[data-dismiss="modal"]').on('click', function() {
        cerrarModal();
    });
    
    // Event listener para cerrar modal con click fuera
    $('#ajustarModal').on('click', function(e) {
        if (e.target === this) {
            cerrarModal();
        }
    });
    
    // Event listener para cerrar con tecla ESC
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            cerrarModal();
        }
    });
    
    $('#ajustarForm').on('submit', function(e) {
        e.preventDefault();
        
        var formData = {
            ingredient_stock_id: $('#ingredientStockId').val(),
            existencia_anterior: $('#existenciaActual').val(),
            nueva_existencia: $('#nuevaExistencia').val(),
            motivo: $('#motivo').val()
        };
        
        $.ajax({
            url: '<?= \yii\helpers\Url::to(['/kpi/ajustar-existencia']) ?>',
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    cerrarModal();
                    location.reload(); // Recargar la página para ver los cambios
                } else {
                    alert('Error: ' + (response.message || 'No se pudo ajustar la existencia'));
                }
            },
            error: function() {
                alert('Error al conectar con el servidor');
            }
        });
    });
});
</script>
