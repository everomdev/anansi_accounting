<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Movement */
/* @var $items array */

$this->title = 'Surtir Requisición #' . $model->requisition_number;
$this->params['breadcrumbs'][] = ['label' => 'Movimientos', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Requisición #' . $model->requisition_number, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Surtir';

?>

<div class="fulfill-requisition">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">
                <i class="bx bx-package"></i> 
                Surtir Requisición #<?= Html::encode($model->requisition_number) ?>
            </h4>
        </div>
        <div class="card-body">
            <!-- Información de la requisición -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <strong>Centro de Consumo:</strong><br>
                    <?= $model->consumptionCenter ? Html::encode($model->consumptionCenter->name) : '-' ?>
                </div>
                <div class="col-md-4">
                    <strong>Fecha Requerida:</strong><br>
                    <?= $model->required_date ? Yii::$app->formatter->asDate($model->required_date, 'php:d/m/Y') : '-' ?>
                </div>
                <div class="col-md-4">
                    <strong>Estado:</strong><br>
                    <span class="badge bg-<?= $model->status === 'fulfilled' ? 'success' : ($model->status === 'partially_fulfilled' ? 'warning' : 'secondary') ?>">
                        <?php
                        $statusLabels = [
                            'pending' => 'Pendiente',
                            'partially_fulfilled' => 'Parcialmente Surtida',
                            'fulfilled' => 'Surtida'
                        ];
                        echo $statusLabels[$model->status] ?? ucfirst($model->status);
                        ?>
                    </span>
                </div>
            </div>

            <?php if ($model->observations): ?>
                <div class="alert alert-info mb-4">
                    <strong>Observaciones:</strong><br>
                    <?= Html::encode($model->observations) ?>
                </div>
            <?php endif; ?>

            <?php $form = ActiveForm::begin([
                'action' => ['convert-to-output', 'id' => $model->id],
                'method' => 'post',
            ]); ?>

            <h5 class="mb-3">Insumos a Surtir</h5>
            
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="fulfill-items-table">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 5%;">#</th>
                            <th style="width: 35%;">Insumo</th>
                            <th style="width: 12%;" class="text-center">Stock Disponible</th>
                            <th style="width: 12%;" class="text-center">Cantidad Solicitada</th>
                            <th style="width: 12%;" class="text-center">Ya Surtido</th>
                            <th style="width: 12%;" class="text-center">Saldo Pendiente</th>
                            <th style="width: 12%;" class="text-center">Surtir Ahora</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $index => $item): 
                            $ingredient = $item->ingredient;
                            $availableStock = $ingredient ? $ingredient->quantity : 0;
                            $alreadyFulfilled = $item->quantity_fulfilled ?? 0;
                            $pending = $item->quantity_requested - $alreadyFulfilled;
                            
                            // Calcular cantidad sugerida (mínimo entre pendiente y stock)
                            $suggestedQuantity = min($pending, $availableStock);
                        ?>
                            <tr data-item-id="<?= $item->id ?>" data-ingredient-id="<?= $item->ingredient_id ?>" 
                                data-pending="<?= $pending ?>" data-available="<?= $availableStock ?>"
                                class="<?= $pending <= 0 ? 'table-success' : ($availableStock < $pending ? 'table-warning' : '') ?>">
                                <td class="text-center"><?= $index + 1 ?></td>
                                <td>
                                    <strong><?= Html::encode($ingredient ? $ingredient->ingredient : 'N/A') ?></strong>
                                    <?php if ($ingredient && $ingredient->brand): ?>
                                        <br><small class="text-muted"><?= Html::encode($ingredient->brand) ?></small>
                                    <?php endif; ?>
                                    <?php if ($ingredient && $ingredient->presentation): ?>
                                        <br><small class="text-muted"><?= Html::encode($ingredient->presentation) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-<?= $availableStock > 0 ? 'info' : 'danger' ?>">
                                        <?= number_format($availableStock, 2) ?> 
                                        <?= Html::encode($ingredient ? ($ingredient->portion_um ?? $ingredient->um) : '') ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <strong><?= number_format($item->quantity_requested, 2) ?></strong>
                                    <?= Html::encode($ingredient ? ($ingredient->portion_um ?? $ingredient->um) : '') ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($alreadyFulfilled > 0): ?>
                                        <span class="badge bg-success">
                                            <?= number_format($alreadyFulfilled, 2) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($pending > 0): ?>
                                        <span class="badge bg-warning text-dark">
                                            <?= number_format($pending, 2) ?>
                                            <?= Html::encode($ingredient ? ($ingredient->portion_um ?? $ingredient->um) : '') ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-success">✓ Completo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($pending > 0): ?>
                                        <input 
                                            type="number" 
                                            name="fulfill_quantities[<?= $item->id ?>]" 
                                            class="form-control form-control-sm text-center fulfill-quantity-input"
                                            value="<?= number_format($suggestedQuantity, 2, '.', '') ?>"
                                            min="0"
                                            max="<?= $pending ?>"
                                            step="0.01"
                                            data-max="<?= $pending ?>"
                                            data-available="<?= $availableStock ?>"
                                            style="font-weight: bold;"
                                        />
                                        <small class="text-muted">Máx: <?= number_format($pending, 2) ?></small>
                                        <?php if ($availableStock < $pending): ?>
                                            <br><small class="text-danger">
                                                <i class="bx bx-error-circle"></i> Stock insuficiente
                                            </small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="6" class="text-end"><strong>Total de Items:</strong></td>
                            <td class="text-center"><strong><?= count($items) ?></strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Observaciones del surtido -->
            <div class="mb-3">
                <label for="fulfill-observations" class="form-label">
                    <i class="bx bx-note"></i> Observaciones del Surtido (opcional)
                </label>
                <?= Html::textarea('fulfill_observations', '', [
                    'id' => 'fulfill-observations',
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'Agregue cualquier observación sobre este surtido...'
                ]) ?>
            </div>

            <!-- Resumen de surtido -->
            <div class="alert alert-light border" id="fulfill-summary" style="display: none;">
                <h6 class="alert-heading">
                    <i class="bx bx-info-circle"></i> Resumen del Surtido
                </h6>
                <div id="summary-content"></div>
            </div>

            <!-- Botones de acción -->
            <div class="d-flex justify-content-between mt-4">
                <?= Html::a(
                    '<i class="bx bx-arrow-back"></i> Cancelar',
                    ['view', 'id' => $model->id],
                    ['class' => 'btn btn-secondary']
                ) ?>
                
                <?= Html::submitButton(
                    '<i class="bx bx-check-circle"></i> Confirmar Surtido',
                    [
                        'class' => 'btn btn-primary btn-lg',
                        'id' => 'submit-fulfill-btn',
                        'data-confirm' => '¿Confirma que desea registrar este surtido?'
                    ]
                ) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

<style>
.fulfill-quantity-input {
    min-width: 100px;
}

.fulfill-quantity-input:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.table-responsive {
    border-radius: 8px;
}

.table thead th {
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.table tbody tr.table-success {
    background-color: #d1e7dd !important;
}

.table tbody tr.table-warning {
    background-color: #fff3cd !important;
}

.card-header h4 {
    display: flex;
    align-items: center;
    gap: 10px;
}
</style>

<?php
$this->registerJs(<<<'JS'
$(document).ready(function() {
    // Validar cantidades al cambiar
    $('.fulfill-quantity-input').on('input change', function() {
        const $input = $(this);
        const value = parseFloat($input.val()) || 0;
        const max = parseFloat($input.data('max'));
        const available = parseFloat($input.data('available'));
        const $row = $input.closest('tr');
        
        // Validar que no exceda el máximo
        if (value > max) {
            $input.val(max.toFixed(2));
            showToast('La cantidad no puede exceder el saldo pendiente', 'warning');
        }
        
        // Advertir sobre stock insuficiente
        if (value > available && available < max) {
            $row.addClass('table-warning');
            showToast('Stock insuficiente para ' + $row.find('strong').first().text(), 'warning');
        } else {
            $row.removeClass('table-warning');
        }
        
        updateSummary();
    });
    
    // Actualizar resumen
    function updateSummary() {
        let totalItems = 0;
        let itemsToFulfill = 0;
        let insufficientStock = 0;
        
        $('.fulfill-quantity-input').each(function() {
            const value = parseFloat($(this).val()) || 0;
            const available = parseFloat($(this).data('available'));
            
            if (value > 0) {
                totalItems++;
                itemsToFulfill++;
                
                if (value > available) {
                    insufficientStock++;
                }
            }
        });
        
        if (itemsToFulfill > 0) {
            let summaryHtml = `
                <div class="row">
                    <div class="col-md-4">
                        <strong>Items a surtir:</strong> ${itemsToFulfill}
                    </div>
            `;
            
            if (insufficientStock > 0) {
                summaryHtml += `
                    <div class="col-md-8">
                        <span class="text-warning">
                            <i class="bx bx-error-circle"></i>
                            <strong>Advertencia:</strong> ${insufficientStock} item(s) con stock insuficiente
                        </span>
                    </div>
                `;
            }
            
            summaryHtml += '</div>';
            
            $('#summary-content').html(summaryHtml);
            $('#fulfill-summary').show();
        } else {
            $('#fulfill-summary').hide();
        }
    }
    
    // Función para mostrar toast
    function showToast(message, type = 'info') {
        const bgClass = type === 'warning' ? 'bg-warning' : (type === 'error' ? 'bg-danger' : 'bg-info');
        const toastHtml = `
            <div class="toast align-items-center text-white ${bgClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
        
        const $toast = $(toastHtml);
        $('#toast-container').append($toast);
        
        const toast = new bootstrap.Toast($toast[0], { delay: 3000 });
        toast.show();
        
        $toast.on('hidden.bs.toast', function() {
            $(this).remove();
        });
    }
    
    // Validación antes de enviar
    $('form').on('submit', function(e) {
        let hasQuantities = false;
        let hasInsufficientStock = false;
        
        $('.fulfill-quantity-input').each(function() {
            const value = parseFloat($(this).val()) || 0;
            const available = parseFloat($(this).data('available'));
            
            if (value > 0) {
                hasQuantities = true;
                
                if (value > available) {
                    hasInsufficientStock = true;
                }
            }
        });
        
        if (!hasQuantities) {
            e.preventDefault();
            alert('Debe especificar al menos una cantidad a surtir.');
            return false;
        }
        
        if (hasInsufficientStock) {
            if (!confirm('Algunos items tienen stock insuficiente. El sistema surtirá solo lo disponible. ¿Desea continuar?')) {
                e.preventDefault();
                return false;
            }
        }
    });
    
    // Inicializar resumen
    updateSummary();
    
    // Atajos de teclado para navegación
    $('.fulfill-quantity-input').on('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const $inputs = $('.fulfill-quantity-input');
            const currentIndex = $inputs.index(this);
            const $nextInput = $inputs.eq(currentIndex + 1);
            
            if ($nextInput.length) {
                $nextInput.focus().select();
            } else {
                $('#submit-fulfill-btn').focus();
            }
        }
    });
    
    // Focus en el primer input
    $('.fulfill-quantity-input').first().focus().select();
});
JS
);
?>

<!-- Contenedor para toasts -->
<div id="toast-container" class="position-fixed top-0 end-0 p-3" style="z-index: 1080;"></div>
