<?php
/** @var $menus array */
/** @var $menuBundleProducts array */
/** @var $rentabilidadReal array */

use yii\helpers\Html;
?>

<div class="menu-comparison">
    <div class="row mb-4">
        <div class="col-12">
            <h5>Comparación de menús</h5>
            <p class="text-muted">
                Mostrando comparación entre 
                <?= count($menus) === 1 ? '1 menú' : '2 menús' ?> seleccionados.
            </p>
        </div>
    </div>
    
    <div class="row">
        <?php foreach ($menus as $menu): ?>
        <div class="col-md-<?= count($menus) === 1 ? '12' : '6' ?>">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="m-0">Menú del <?= Yii::$app->formatter->asDate($menu->date) ?></h5>
                </div>
                <div class="card-body">
                    <h6>Rentabilidad Teórica por Categoría</h6>
                    <?php if (isset($menuBundleProducts[$menu->id])): ?>
                        <div class="rentabilidad-wrapper mb-3">
                            <?php foreach ($menuBundleProducts[$menu->id] as $category => $value): 
                                $formattedValue = floatval($value);
                                $colorClass = $formattedValue <= 25 ? 'text-success' : 
                                             ($formattedValue <= 40 ? 'text-warning' : 'text-danger');
                            ?>
                                <div class="rentabilidad-item mb-1">
                                    <span class="badge bg-secondary me-2"><?= Html::encode($category) ?></span>
                                    <span class="<?= $colorClass ?> fw-bold">
                                        <?= Html::encode(number_format($formattedValue, 2)) ?>%
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <?php
                        // Calcular promedio teórico
                        $values = array_values($menuBundleProducts[$menu->id]);
                        $average = array_sum(array_map('floatval', $values)) / count($values);
                        $colorClass = $average <= 25 ? 'bg-success' : 
                                     ($average <= 40 ? 'bg-warning' : 'bg-danger');
                        ?>
                        <div class="mb-4">
                            <h6>Rentabilidad Teórica Promedio</h6>
                            <span class="badge <?= $colorClass ?> fs-6">
                                <?= number_format($average, 2) ?>%
                            </span>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No hay datos de rentabilidad teórica disponibles</p>
                    <?php endif; ?>
                    
                    <hr>
                    
                    <h6>Rentabilidad Real por Categoría</h6>
                    <?php if (isset($rentabilidadReal[$menu->id])): ?>
                        <div class="rentabilidad-wrapper mb-3">
                            <?php foreach ($rentabilidadReal[$menu->id] as $category => $value): 
                                $formattedValue = floatval($value);
                                $colorClass = $formattedValue <= 25 ? 'text-success' : 
                                             ($formattedValue <= 40 ? 'text-warning' : 'text-danger');
                            ?>
                                <div class="rentabilidad-item mb-1">
                                    <span class="badge bg-secondary me-2"><?= Html::encode($category) ?></span>
                                    <span class="<?= $colorClass ?> fw-bold">
                                        <?= Html::encode(number_format($formattedValue, 2)) ?>%
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <?php
                        // Calcular promedio real
                        $values = array_values($rentabilidadReal[$menu->id]);
                        $average = array_sum(array_map('floatval', $values)) / count($values);
                        $colorClass = $average <= 25 ? 'bg-success' : 
                                     ($average <= 40 ? 'bg-warning' : 'bg-danger');
                        ?>
                        <div>
                            <h6>Rentabilidad Real Promedio</h6>
                            <span class="badge <?= $colorClass ?> fs-6">
                                <?= number_format($average, 2) ?>%
                            </span>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No hay datos de rentabilidad real disponibles</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>