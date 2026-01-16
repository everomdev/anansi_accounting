<?php
/** @var $this \yii\web\View */

use yii\helpers\Html;

$this->title = Yii::t('app', 'Reglas de Requisición por Centro de Consumo');
$this->params['breadcrumbs'][] = $this->title;

$businessData = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);
$business = \common\models\Business::findOne(['id' => $businessData['id']]);

if (!$business) {
    echo '<div class="alert alert-danger">No se pudo cargar la información del negocio.</div>';
    return;
}
?>

<div class="requisition-rules-page">
    <div class="card">
        
        <div class="card-body">
            <?= $this->render('tabs/_requisition_rules', [
                'model' => new \backend\models\UpdateAccountForm([
                    'businessId' => $business->id
                ]),
            ]) ?>
        </div>
    </div>
</div>

<style>
.requisition-rules-page .card {
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
}

.requisition-rules-page .card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 12px 12px 0 0;
    padding: 1.5rem;
}

.requisition-rules-page .card-header h4 {
    color: white;
    margin: 0;
}

.requisition-rules-page .card-header p {
    color: rgba(255, 255, 255, 0.9);
}

.requisition-rules-page .card-body {
    padding: 2rem;
}
</style>
