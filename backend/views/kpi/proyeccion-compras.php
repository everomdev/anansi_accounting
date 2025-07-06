<?php

use yii\helpers\Html;

/* @var $this yii\web\View */

$this->title = 'Proyección de Compras Inteligentes';
$this->params['breadcrumbs'][] = ['label' => 'KPI\'s y Control', 'url' => ['#']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="kpi-proyeccion-compras">
    
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-shopping-cart"></i>
                        <?= Html::encode($this->title) ?>
                    </h3>
                </div>
                <div class="card-body">
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Próximamente:</strong> Esta funcionalidad estará disponible en una futura actualización.
                        <br>
                        <small>Permitirá generar proyecciones inteligentes de compras basadas en el historial de ventas y consumos.</small>
                    </div>
                    
                    <div class="text-center">
                        <i class="fas fa-tools" style="font-size: 4em; color: #ccc;"></i>
                        <h4 class="mt-3">En construcción</h4>
                        <p class="text-muted">Estamos trabajando en esta funcionalidad para ofrecerte la mejor experiencia.</p>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
    
</div>
