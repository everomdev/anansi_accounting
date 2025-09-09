<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use common\models\User as CustomUser;

/**
 * @var $this yii\web\View
 * @var $user Da\User\Model\User
 * @var $customUser common\models\User
 * @var $subscriptionDetails array|null
 * @var $rawSubscription \Stripe\Subscription|null
 */

$this->title = 'Detalles del Usuario: ' . $user->username;
$this->params['breadcrumbs'][] = ['label' => 'Administrar usuarios', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Función helper para formatear fechas
function formatDate($timestamp) {
    if (!$timestamp) return 'N/A';
    return date('d/m/Y H:i:s', $timestamp);
}

// Función helper para formatear estado
function getStatusBadge($status) {
    $badges = [
        'active' => '<span class="badge badge-success">Activa</span>',
        'canceled' => '<span class="badge badge-danger">Cancelada</span>',
        'past_due' => '<span class="badge badge-warning">Vencida</span>',
        'incomplete' => '<span class="badge badge-info">Incompleta</span>',
        'trialing' => '<span class="badge badge-primary">En prueba</span>',
        'unpaid' => '<span class="badge badge-danger">No pagada</span>',
    ];
    
    return $badges[$status] ?? '<span class="badge badge-secondary">' . ucfirst($status) . '</span>';
}
?>

<?php $this->beginContent('@Da/User/resources/views/shared/admin_layout.php') ?>

<style>
.user-view .card {
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    margin-bottom: 1rem;
}

.user-view .badge {
    font-size: 0.85em;
}

.user-view pre {
    font-size: 0.8em;
    max-height: 200px;
    overflow-y: auto;
}

.user-view .table-sm td {
    padding: 0.3rem;
    border-top: 1px solid #dee2e6;
}

.user-view .table-sm td:first-child {
    font-weight: 500;
    width: 40%;
}

.user-view .card-header h5 {
    margin-bottom: 0;
}

.user-view .alert {
    border-left: 4px solid;
}

.user-view .alert-warning {
    border-left-color: #ffc107;
}

.user-view .text-muted {
    font-style: italic;
}

@media (max-width: 768px) {
    .user-view .card-tools .btn {
        margin-bottom: 0.5rem;
    }
}
</style>

<div class="user-view">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user"></i> <?= Html::encode($user->username) ?>
                    </h3>
                    <div class="card-tools">
                        <?= Html::a('Volver a la lista', ['index'], ['class' => 'btn btn-secondary btn-sm']) ?>
                        <?= Html::a('Editar usuario', ['update', 'id' => $user->id], ['class' => 'btn btn-primary btn-sm']) ?>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Información básica del usuario -->
                        <div class="col-md-6">
                            <h4><i class="fas fa-info-circle"></i> Información Básica</h4>
                            <?= DetailView::widget([
                                'model' => $user,
                                'attributes' => [
                                    'id',
                                    'username',
                                    'email:email',
                                    [
                                        'attribute' => 'created_at',
                                        'value' => formatDate($user->created_at),
                                        'label' => 'Fecha de registro',
                                    ],
                                    [
                                        'attribute' => 'last_login_at',
                                        'value' => $user->last_login_at ? formatDate($user->last_login_at) : 'Nunca',
                                        'label' => 'Último acceso',
                                    ],
                                    [
                                        'attribute' => 'last_login_ip',
                                        'value' => $user->last_login_ip ?: 'N/A',
                                        'label' => 'Última IP',
                                    ],
                                    [
                                        'attribute' => 'confirmed_at',
                                        'value' => $user->confirmed_at ? formatDate($user->confirmed_at) : 'No confirmado',
                                        'label' => 'Email confirmado',
                                    ],
                                    [
                                        'attribute' => 'blocked_at',
                                        'value' => $user->blocked_at ? formatDate($user->blocked_at) : 'No bloqueado',
                                        'label' => 'Estado de bloqueo',
                                    ],
                                ],
                            ]) ?>
                        </div>

                        <!-- Información del plan -->
                        <div class="col-md-6">
                            <h4><i class="fas fa-credit-card"></i> Plan y Suscripción</h4>
                            <?php if ($customUser && $customUser->userPlan): ?>
                                <?= DetailView::widget([
                                    'model' => $customUser->userPlan,
                                    'attributes' => [
                                        [
                                            'label' => 'Plan actual',
                                            'value' => $customUser->userPlan->plan ? $customUser->userPlan->plan->name : 'Sin plan',
                                        ],
                                        [
                                            'label' => 'Estado de suscripción',
                                            'value' => getStatusBadge($customUser->userPlan->stripe_subscription_status ?: 'N/A'),
                                            'format' => 'raw',
                                        ],
                                        'stripe_customer_id',
                                        'stripe_subscription_id',
                                    ],
                                ]) ?>
                            <?php else: ?>
                                <p class="text-muted">El usuario no tiene plan asignado.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($subscriptionDetails): ?>
                    <!-- Detalles de la suscripción de Stripe -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h4><i class="fab fa-stripe"></i> Detalles de Stripe</h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card card-info">
                                        <div class="card-header">
                                            <h5 class="card-title">Información de Facturación</h5>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm">
                                                <tr>
                                                    <td><strong>Estado:</strong></td>
                                                    <td><?= getStatusBadge($subscriptionDetails['status']) ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Monto:</strong></td>
                                                    <td>$<?= number_format($subscriptionDetails['amount'], 2) ?> <?= strtoupper($subscriptionDetails['currency']) ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Tipo de facturación:</strong></td>
                                                    <td>
                                                        <span class="badge badge-<?= $subscriptionDetails['billing_interval'] === 'year' ? 'warning' : 'info' ?>">
                                                            <?= $subscriptionDetails['billing_interval'] === 'year' ? 'Anual' : 'Mensual' ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Cancelar al final del período:</strong></td>
                                                    <td>
                                                        <?php if ($subscriptionDetails['cancel_at_period_end']): ?>
                                                            <span class="badge badge-warning">Sí</span>
                                                        <?php else: ?>
                                                            <span class="badge badge-success">No</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="card card-success">
                                        <div class="card-header">
                                            <h5 class="card-title">Fechas Importantes</h5>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm">
                                                <tr>
                                                    <td><strong>Inicio del período actual:</strong></td>
                                                    <td><?= formatDate($subscriptionDetails['current_period_start']) ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Fin del período actual:</strong></td>
                                                    <td><?= formatDate($subscriptionDetails['current_period_end']) ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Próximo pago:</strong></td>
                                                    <td>
                                                        <?php if ($subscriptionDetails['status'] === 'active'): ?>
                                                            <?php 
                                                            $nextPayment = $subscriptionDetails['next_payment_date'];
                                                            $daysUntil = ceil(($nextPayment - time()) / (60 * 60 * 24));
                                                            ?>
                                                            <?= formatDate($nextPayment) ?>
                                                            <?php if ($daysUntil <= 7 && $daysUntil > 0): ?>
                                                                <span class="badge badge-warning ml-2"><?= $daysUntil ?> días</span>
                                                            <?php elseif ($daysUntil <= 0): ?>
                                                                <span class="badge badge-danger ml-2">Vencido</span>
                                                            <?php else: ?>
                                                                <span class="badge badge-success ml-2"><?= $daysUntil ?> días</span>
                                                            <?php endif; ?>
                                                        <?php else: ?>
                                                            <span class="text-muted">N/A</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <?php if ($subscriptionDetails['trial_end']): ?>
                                                <tr>
                                                    <td><strong>Período de prueba:</strong></td>
                                                    <td>
                                                        <?= formatDate($subscriptionDetails['trial_start']) ?> - 
                                                        <?= formatDate($subscriptionDetails['trial_end']) ?>
                                                        <?php if ($subscriptionDetails['trial_end'] > time()): ?>
                                                            <span class="badge badge-info ml-2">En curso</span>
                                                        <?php else: ?>
                                                            <span class="badge badge-secondary ml-2">Finalizado</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <?php endif; ?>
                                                <?php if ($subscriptionDetails['canceled_at']): ?>
                                                <tr>
                                                    <td><strong>Fecha de cancelación:</strong></td>
                                                    <td><?= formatDate($subscriptionDetails['canceled_at']) ?></td>
                                                </tr>
                                                <?php endif; ?>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($rawSubscription): ?>
                    <!-- Información técnica adicional -->
                    <!-- <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card card-secondary">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-cog"></i> Información Técnica
                                        <button class="btn btn-sm btn-outline-light ml-2" type="button" data-toggle="collapse" data-target="#technicalInfo">
                                            Mostrar/Ocultar
                                        </button>
                                    </h5>
                                </div>
                                <div class="collapse" id="technicalInfo">
                                    <div class="card-body">
                                        <div class="row mb-4">
                                            <div class="col-md-6">
                                                <h6><i class="fas fa-database"></i> Datos de la Base de Datos</h6>
                                                <table class="table table-sm">
                                                    <tr>
                                                        <td><strong>User ID:</strong></td>
                                                        <td><code><?= $user->id ?></code></td>
                                                    </tr>
                                                    <?php if ($customUser && $customUser->userPlan): ?>
                                                    <tr>
                                                        <td><strong>Plan ID:</strong></td>
                                                        <td><code><?= $customUser->userPlan->plan_id ?></code></td>
                                                    </tr>
                                                    <?php if ($customUser->userPlan->stripe_customer_id): ?>
                                                    <tr>
                                                        <td><strong>Stripe Customer ID:</strong></td>
                                                        <td><code><?= $customUser->userPlan->stripe_customer_id ?></code></td>
                                                    </tr>
                                                    <?php endif; ?>
                                                    <?php if ($customUser->userPlan->stripe_subscription_id): ?>
                                                    <tr>
                                                        <td><strong>Stripe Subscription ID:</strong></td>
                                                        <td><code><?= $customUser->userPlan->stripe_subscription_id ?></code></td>
                                                    </tr>
                                                    <?php endif; ?>
                                                    <?php endif; ?>
                                                    <tr>
                                                        <td><strong>Estado de confirmación:</strong></td>
                                                        <td>
                                                            <?php if ($user->isConfirmed): ?>
                                                                <span class="badge badge-success">✓ Confirmado</span>
                                                            <?php else: ?>
                                                                <span class="badge badge-warning">⚠ Sin confirmar</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Estado de bloqueo:</strong></td>
                                                        <td>
                                                            <?php if ($user->isBlocked): ?>
                                                                <span class="badge badge-danger">🚫 Bloqueado</span>
                                                            <?php else: ?>
                                                                <span class="badge badge-success">✓ Activo</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                    <?php if ($user->last_login_ip): ?>
                                                    <tr>
                                                        <td><strong>Última IP:</strong></td>
                                                        <td><code><?= $user->last_login_ip ?></code></td>
                                                    </tr>
                                                    <?php endif; ?>
                                                </table>
                                            </div>
                                            <div class="col-md-6">
                                                <h6><i class="fas fa-clock"></i> Fechas de Auditoría</h6>
                                                <table class="table table-sm">
                                                    <tr>
                                                        <td><strong>Fecha de registro:</strong></td>
                                                        <td>
                                                            <?php if (extension_loaded('intl')): ?>
                                                                <?= Yii::t('usuario', '{0, date, dd MMM yyyy HH:mm}', [$user->created_at]) ?>
                                                            <?php else: ?>
                                                                <?= date('d M Y H:i', $user->created_at) ?>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                    <?php if ($user->updated_at): ?>
                                                    <tr>
                                                        <td><strong>Última actualización:</strong></td>
                                                        <td>
                                                            <?php if (extension_loaded('intl')): ?>
                                                                <?= Yii::t('usuario', '{0, date, dd MMM yyyy HH:mm}', [$user->updated_at]) ?>
                                                            <?php else: ?>
                                                                <?= date('d M Y H:i', $user->updated_at) ?>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                    <?php endif; ?>
                                                    <tr>
                                                        <td><strong>Último acceso:</strong></td>
                                                        <td>
                                                            <?php if (!$user->last_login_at || $user->last_login_at == 0): ?>
                                                                <span class="text-muted">Nunca</span>
                                                            <?php elseif (extension_loaded('intl')): ?>
                                                                <?= Yii::t('usuario', '{0, date, dd MMM yyyy HH:mm}', [$user->last_login_at]) ?>
                                                            <?php else: ?>
                                                                <?= date('d M Y H:i', $user->last_login_at) ?>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                    <?php if ($rawSubscription && isset($rawSubscription->created)): ?>
                                                    <tr>
                                                        <td><strong>Suscripción creada:</strong></td>
                                                        <td><?= date('d M Y H:i', $rawSubscription->created) ?></td>
                                                    </tr>
                                                    <?php endif; ?>
                                                    <?php if ($rawSubscription && isset($rawSubscription->canceled_at) && $rawSubscription->canceled_at): ?>
                                                    <tr>
                                                        <td><strong>Suscripción cancelada:</strong></td>
                                                        <td><?= date('d M Y H:i', $rawSubscription->canceled_at) ?></td>
                                                    </tr>
                                                    <?php endif; ?>
                                                </table>
                                            </div>
                                        </div>
                                        
                                        <hr>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6><i class="fas fa-tag"></i> Metadatos de Stripe</h6>
                                                <?php if (isset($rawSubscription->metadata)): ?>
                                                    <pre class="bg-light p-2"><?= Html::encode(print_r($rawSubscription->metadata->toArray(), true)) ?></pre>
                                                <?php else: ?>
                                                    <p class="text-muted">No hay metadatos disponibles</p>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-6">
                                                <h6><i class="fas fa-building"></i> Información del Negocio</h6>
                                                <?php 
                                                try {
                                                    $business = $customUser ? $customUser->getBusiness() : null;
                                                    if ($business): ?>
                                                        <table class="table table-sm">
                                                            <tr>
                                                                <td><strong>Negocio ID:</strong></td>
                                                                <td><code><?= $business->id ?></code></td>
                                                            </tr>
                                                            <tr>
                                                                <td><strong>Nombre del negocio:</strong></td>
                                                                <td><?= Html::encode($business->name ?? 'Sin nombre') ?></td>
                                                            </tr>
                                                            <?php if (isset($business->created_at)): ?>
                                                            <tr>
                                                                <td><strong>Negocio creado:</strong></td>
                                                                <td><?= date('d M Y', $business->created_at) ?></td>
                                                            </tr>
                                                            <?php endif; ?>
                                                        </table>
                                                    <?php else: ?>
                                                        <p class="text-muted">No tiene negocio asociado</p>
                                                    <?php endif;
                                                } catch (Exception $e) { ?>
                                                    <p class="text-muted">Error al cargar información del negocio</p>
                                                <?php } ?>
                                                
                                                <hr>
                                                
                                                <h6><i class="fas fa-key"></i> Permisos y Límites</h6>
                                                <?php if ($customUser && $customUser->plan): ?>
                                                    <table class="table table-sm">
                                                        <tr>
                                                            <td><strong>Recetas permitidas:</strong></td>
                                                            <td>
                                                                <span class="badge badge-info"><?= $customUser->plan->recetas ?? 'N/A' ?></span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Subrecetas permitidas:</strong></td>
                                                            <td>
                                                                <span class="badge badge-info"><?= $customUser->plan->subrecetas ?? 'N/A' ?></span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Usuarios permitidos:</strong></td>
                                                            <td>
                                                                <span class="badge badge-info"><?= $customUser->plan->users ?? 'N/A' ?></span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Combos permitidos:</strong></td>
                                                            <td>
                                                                <span class="badge badge-info"><?= $customUser->plan->combos ?? 'N/A' ?></span>
                                                            </td>
                                                        </tr>
                                                        <?php if (isset($customUser->plan->convoy)): ?>
                                                        <tr>
                                                            <td><strong>Convoy permitido:</strong></td>
                                                            <td>
                                                                <span class="badge badge-info"><?= $customUser->plan->convoy ?></span>
                                                            </td>
                                                        </tr>
                                                        <?php endif; ?>
                                                        <?php if (isset($customUser->plan->trial_days)): ?>
                                                        <tr>
                                                            <td><strong>Días de prueba:</strong></td>
                                                            <td>
                                                                <span class="badge badge-secondary"><?= $customUser->plan->trial_days ?> días</span>
                                                            </td>
                                                        </tr>
                                                        <?php endif; ?>
                                                    </table>
                                                <?php else: ?>
                                                    <p class="text-muted">No hay información de plan disponible</p>
                                                <?php endif; ?>
                                                
                                                <hr>
                                                
                                                <h6><i class="fas fa-code"></i> Información del Producto Stripe</h6>
                                                <?php if (isset($rawSubscription->items->data[0])): ?>
                                                    <?php $item = $rawSubscription->items->data[0]; ?>
                                                    <table class="table table-sm">
                                                        <tr>
                                                            <td><strong>Price ID:</strong></td>
                                                            <td><code><?= $item->price->id ?></code></td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Product ID:</strong></td>
                                                            <td><code><?= $item->price->product ?></code></td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Cantidad:</strong></td>
                                                            <td><?= $item->quantity ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Precio activo:</strong></td>
                                                            <td>
                                                                <?php if ($item->price->active): ?>
                                                                    <span class="badge badge-success">✓ Sí</span>
                                                                <?php else: ?>
                                                                    <span class="badge badge-danger">✗ No</span>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                <?php else: ?>
                                                    <p class="text-muted">No hay información de producto disponible</p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> -->
                    <?php endif; ?>

                    <?php if (!$subscriptionDetails && $customUser && $customUser->userPlan && $customUser->userPlan->stripe_subscription_id): ?>
                    <!-- Error al obtener datos de Stripe -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Advertencia:</strong> No se pudieron obtener los detalles de la suscripción desde Stripe. 
                                Esto puede deberse a que la suscripción fue eliminada o hay un problema de conectividad.
                                <br>
                                <strong>ID de suscripción:</strong> <code><?= $customUser->userPlan->stripe_subscription_id ?></code>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->endContent() ?>

<script>
// Auto-refresh de los datos cada 30 segundos si hay una suscripción activa
<?php if ($subscriptionDetails && $subscriptionDetails['status'] === 'active'): ?>
setTimeout(function() {
    location.reload();
}, 30000);
<?php endif; ?>
</script>
