<?php

/*
 * This file is part of the 2amigos/yii2-usuario project.
 *
 * (c) 2amigOS! <http://2amigos.us/>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\Pjax;
use common\models\User as CustomUser; // Importar nuestro modelo personalizado

/**
 * @var $this         yii\web\View
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel  Da\User\Search\UserSearch
 * @var $module       Da\User\Module
 */

$this->title = Yii::t('usuario', 'Manage users');
$this->params['breadcrumbs'][] = $this->title;

$module = Yii::$app->getModule('user');
?>

<?php  $this->beginContent('@Da/User/resources/views/shared/admin_layout.php') ?>

<?php Pjax::begin() ?>
<style>
.fixed-table-container {
    position: relative;
    overflow: auto;
    max-width: 100%;
    max-height: 70vh; /* Altura máxima para activar scroll */
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
}

.fixed-table {
    border-collapse: separate;
    border-spacing: 0;
    width: 100%;
    margin: 0;
}

/* Headers fijos en la parte superior */
.fixed-table thead th {
    position: sticky;
    top: 0;
    background: #e9ecef;
    z-index: 5;
    border-bottom: 2px solid #dee2e6;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    font-weight: 600;
    white-space: nowrap;
    padding: 12px 8px;
}

/* Primera columna fija a la izquierda */
.fixed-table th:first-child,
.fixed-table td:first-child {
    position: sticky;
    left: 0;
    background: #f8f9fa;
    z-index: 3;
    min-width: 120px;
    border-right: 2px solid #dee2e6;
    box-shadow: 2px 0 4px rgba(0,0,0,0.1);
}

/* Header de la primera columna (fijo tanto arriba como a la izquierda) */
.fixed-table th:first-child {
    background: #e9ecef;
    z-index: 6; /* Menor que el dropdown del navbar */
    border-right: 2px solid #dee2e6;
    border-bottom: 2px solid #dee2e6;
    box-shadow: 2px 2px 4px rgba(0,0,0,0.15);
}

/* Estilos para las celdas */
.fixed-table td {
    padding: 8px;
    border-bottom: 1px solid #dee2e6;
    vertical-align: middle;
    white-space: nowrap;
}

/* Hover effect para las filas */
.fixed-table tbody tr:hover {
    background-color: #f5f5f5;
}

.fixed-table tbody tr:hover td:first-child {
    background-color: #e9ecef;
}

/* Asegurar que los modales tengan prioridad sobre la tabla sticky */
.modal {
    z-index: 1050 !important;
}

.modal-backdrop {
    z-index: 1040 !important;
}

/* Mejorar responsive */
@media (max-width: 768px) {
    .fixed-table-container {
        max-height: 60vh;
    }
    
    .fixed-table th,
    .fixed-table td {
        padding: 6px 4px;
        font-size: 0.875rem;
    }
}

/* Scrollbar personalizado */
.fixed-table-container::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

.fixed-table-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.fixed-table-container::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}

.fixed-table-container::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Paginador fijo en la parte inferior */
.pagination-container {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: #ffffff;
    border-top: 2px solid #dee2e6;
    box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
    z-index: 1000;
    padding: 15px 20px;
    text-align: center;
}

.pagination-container .pagination {
    margin: 0;
    justify-content: center;
}

.pagination-container .pagination .page-link {
    border-radius: 0.375rem;
    margin: 0 2px;
    border: 1px solid #dee2e6;
    color: #495057;
    transition: all 0.15s ease-in-out;
}

.pagination-container .pagination .page-link:hover {
    background-color: #e9ecef;
    border-color: #adb5bd;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.pagination-container .pagination .page-item.active .page-link {
    background-color: #007bff;
    border-color: #007bff;
    color: white;
    box-shadow: 0 2px 4px rgba(0,123,255,0.3);
}

/* Agregar padding inferior al contenido para evitar que se oculte detrás del paginador */
body {
    padding-bottom: 80px;
}

/* Estilos responsive para el paginador */
@media (max-width: 768px) {
    .pagination-container {
        padding: 10px 15px;
    }
    
    .pagination-container .pagination .page-link {
        padding: 0.375rem 0.5rem;
        font-size: 0.875rem;
    }
    
    body {
        padding-bottom: 70px;
    }
}

@media (max-width: 576px) {
    .pagination-container {
        padding: 8px 10px;
    }
    
    .pagination-container .pagination .page-link {
        padding: 0.25rem 0.375rem;
        font-size: 0.75rem;
        margin: 0 1px;
    }
    
    body {
        padding-bottom: 60px;
    }
}

/* Estilos para el modal de confirmación */
#confirmDeleteModal {
    z-index: 9999 !important; /* Asegurar que esté por encima del navbar */
}

#confirmDeleteModal .modal-backdrop {
    z-index: 9998 !important; /* Backdrop justo debajo del modal */
}

#confirmDeleteModal .modal-content {
    border: none;
    border-radius: 10px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

/* Estilos específicos para vista móvil */
@media (max-width: 768px) {
    #confirmDeleteModal {
        z-index: 9999 !important;
        padding-top: 20px; /* Espacio extra arriba en móvil */
    }
    
    #confirmDeleteModal .modal-dialog {
        margin: 20px auto;
        max-height: calc(100vh - 40px);
    }
    
    #confirmDeleteModal .modal-content {
        max-height: calc(100vh - 60px);
        overflow-y: auto;
    }
}

#confirmDeleteModal .modal-header {
    border-radius: 10px 10px 0 0;
    border-bottom: none;
}

#confirmDeleteModal .modal-body {
    padding: 2rem;
}

#confirmDeleteModal .alert {
    border: none;
    border-radius: 8px;
}

#confirmDeleteModal .alert-warning {
    background-color: #fff3cd;
    color: #856404;
}

#confirmDeleteModal .alert-danger {
    background-color: #f8d7da;
    color: #721c24;
}

#confirmDeleteModal .btn {
    border-radius: 6px;
    font-weight: 500;
    padding: 0.5rem 1.5rem;
    transition: all 0.2s ease;
}

#confirmDeleteModal .btn-danger:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
}

#confirmDeleteModal .fa-user-times {
    animation: pulse 1s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}
</style>
<div class="fixed-table-container">
<?= GridView::widget(
    [
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,        'layout' => "{items}\n<div class='pagination-container'>{pager}</div>",
        'tableOptions' => ['class' => 'table table-striped fixed-table'],
        'columns' => [
            [
                'attribute' => 'username',
                'headerOptions' => ['style' => 'min-width: 120px;'],
                'contentOptions' => ['style' => 'font-weight: 600;'],
            ],
            'email:email',
            [
                'attribute' => 'created_at',
                'label' => 'Fecha de registro',
                'value' => function ($model) {
                    if (extension_loaded('intl')) {
                        return Yii::t('usuario', '{0, date, MMM dd, YYYY HH:mm}', [$model->created_at]);
                    }
                    return date('Y-m-d G:i:s', $model->created_at);
                },
                'headerOptions' => ['style' => 'min-width: 140px;'],
            ],
            [
                'label' => 'Plan actual',
                'value' => function ($model) {
                    // Usar nuestro modelo personalizado para obtener la relación
                    $customUser = CustomUser::findOne($model->id);
                    if ($customUser && $customUser->userPlan && $customUser->userPlan->plan) {
                        return $customUser->userPlan->plan->name;
                    }
                    return '<span class="text-muted">Sin plan</span>';
                },
                'format' => 'raw',
                'headerOptions' => ['style' => 'min-width: 120px;'],
            ],
            [
                'label' => 'Estado de suscripción',
                'value' => function ($model) {
                    // Usar nuestro modelo personalizado para obtener la relación
                    $customUser = CustomUser::findOne($model->id);
                    if ($customUser && $customUser->userPlan && $customUser->userPlan->stripe_subscription_status) {
                        $status = $customUser->userPlan->stripe_subscription_status;
                        $class = '';
                        $text = '';
                        
                        // Obtener detalles adicionales de Stripe
                        $subscriptionDetails = $customUser->getSubscriptionDetails();
                        $additionalInfo = '';
                        
                        if ($subscriptionDetails && $status === 'canceled') {
                            if ($subscriptionDetails['ended_at']) {
                                $endedDate = date('d/m/Y', $subscriptionDetails['ended_at']);
                                $additionalInfo = " (Terminó: $endedDate)";
                            } elseif ($subscriptionDetails['canceled_at']) {
                                $canceledDate = date('d/m/Y', $subscriptionDetails['canceled_at']);
                                $additionalInfo = " (Cancelada: $canceledDate)";
                            }
                        }
                        
                        switch ($status) {
                            case 'active':
                                $class = 'text-success';
                                $text = 'Activa';
                                break;
                            case 'canceled':
                                $class = 'text-danger';
                                $text = 'Cancelada';
                                break;
                            case 'past_due':
                                $class = 'text-warning';
                                $text = 'Vencida';
                                break;
                            case 'incomplete':
                                $class = 'text-info';
                                $text = 'Incompleta';
                                break;
                            case 'trialing':
                                $class = 'text-primary';
                                $text = 'En prueba';
                                break;
                            case 'unpaid':
                                $class = 'text-danger';
                                $text = 'No pagada';
                                break;
                            default:
                                $class = 'text-secondary';
                                $text = ucfirst($status);
                        }
                        
                        return '<span class="' . $class . '">' . $text . $additionalInfo . '</span>';
                    }
                    return '<span class="text-muted">No activa</span>';
                },
                'format' => 'raw',
                'headerOptions' => ['style' => 'min-width: 160px;'],
            ],
            [
                'label' => 'Tipo de facturación',
                'value' => function ($model) {
                    // Usar nuestro modelo personalizado para obtener información detallada
                    $customUser = CustomUser::findOne($model->id);
                    if ($customUser && $customUser->userPlan && $customUser->userPlan->stripe_subscription_id) {
                        $subscriptionDetails = $customUser->getSubscriptionDetails();
                        if ($subscriptionDetails) {
                            $interval = $subscriptionDetails['billing_interval'];
                            $amount = $subscriptionDetails['amount'];
                            $currency = $subscriptionDetails['currency'];
                            
                            $intervalText = $interval === 'year' ? 'Anual' : 'Mensual';
                            return "$intervalText - $$amount $currency";
                        }
                    }
                    
                    // Fallback a la lógica anterior si no hay datos de Stripe
                    if ($customUser && $customUser->userPlan && $customUser->userPlan->plan) {
                        $plan = $customUser->userPlan->plan;
                        if ($plan->monthly_price > 0 && $plan->yearly_price > 0) {
                            return 'Mensual/Anual disponible';
                        } elseif ($plan->monthly_price > 0) {
                            return 'Mensual';
                        } elseif ($plan->yearly_price > 0) {
                            return 'Anual';
                        }
                        return 'Gratuito';
                    }
                    return '<span class="text-muted">N/A</span>';
                },
                'format' => 'raw',
                'headerOptions' => ['style' => 'min-width: 140px;'],
            ],
            [
                'label' => 'Próximo pago / Vencimiento',
                'value' => function ($model) {
                    // Usar nuestro modelo personalizado para obtener información detallada
                    $customUser = CustomUser::findOne($model->id);
                    if ($customUser && $customUser->userPlan && $customUser->userPlan->stripe_subscription_id) {
                        $subscriptionDetails = $customUser->getSubscriptionDetails();
                        if ($subscriptionDetails) {
                            $status = $subscriptionDetails['status'];
                            
                            // Suscripción activa
                            if ($status === 'active') {
                                if ($subscriptionDetails['cancel_at_period_end']) {
                                    // Cancelada pero sigue activa hasta el final del período
                                    $endDate = $subscriptionDetails['current_period_end'];
                                    $date = date('d/m/Y', $endDate);
                                    $daysUntil = ceil(($endDate - time()) / (60 * 60 * 24));
                                    return '<span class="text-warning">Termina: ' . $date . ' (' . $daysUntil . ' días)</span>';
                                } else {
                                    // Suscripción activa normal
                                    $nextPayment = $subscriptionDetails['next_payment_date'];
                                    if ($nextPayment) {
                                        $date = date('d/m/Y', $nextPayment);
                                        $daysUntil = ceil(($nextPayment - time()) / (60 * 60 * 24));
                                        
                                        if ($daysUntil <= 0) {
                                            return '<span class="text-danger">Vencida</span>';
                                        } elseif ($daysUntil <= 7) {
                                            return '<span class="text-warning">' . $date . ' (' . $daysUntil . ' días)</span>';
                                        } else {
                                            return '<span class="text-success">' . $date . ' (' . $daysUntil . ' días)</span>';
                                        }
                                    }
                                }
                            }
                            // En período de prueba
                            elseif ($status === 'trialing') {
                                $trialEnd = $subscriptionDetails['trial_end'];
                                if ($trialEnd) {
                                    $date = date('d/m/Y', $trialEnd);
                                    $daysUntil = ceil(($trialEnd - time()) / (60 * 60 * 24));
                                    if ($daysUntil <= 0) {
                                        return '<span class="text-danger">Prueba expirada</span>';
                                    } elseif ($daysUntil <= 3) {
                                        return '<span class="text-warning">Fin prueba: ' . $date . ' (' . $daysUntil . ' días)</span>';
                                    } else {
                                        return '<span class="text-info">Fin prueba: ' . $date . ' (' . $daysUntil . ' días)</span>';
                                    }
                                }
                            }
                            // Suscripción cancelada
                            elseif ($status === 'canceled') {
                                if ($subscriptionDetails['ended_at']) {
                                    $endedDate = date('d/m/Y', $subscriptionDetails['ended_at']);
                                    return '<span class="text-muted">Terminó: ' . $endedDate . '</span>';
                                } elseif ($subscriptionDetails['canceled_at']) {
                                    $canceledDate = date('d/m/Y', $subscriptionDetails['canceled_at']);
                                    return '<span class="text-muted">Cancelada: ' . $canceledDate . '</span>';
                                }
                                return '<span class="text-muted">Cancelada</span>';
                            }
                            // Otros estados
                            else {
                                return '<span class="text-secondary">' . ucfirst($status) . '</span>';
                            }
                        }
                    }
                    return '<span class="text-muted">N/A</span>';
                },
                'format' => 'raw',
                'headerOptions' => ['style' => 'min-width: 160px;'],
            ],
             [
                 'attribute' => 'last_login_at',
                 'value' => function ($model) {
                     if (!$model->last_login_at || $model->last_login_at == 0) {
                         return Yii::t('usuario', 'Never');
                     } elseif (extension_loaded('intl')) {
                         return Yii::t('usuario', '{0, date, MMM dd, YYYY HH:mm}', [$model->last_login_at]);
                     } else {
                         return date('Y-m-d G:i:s', $model->last_login_at);
                     }
                 },
             ],
            // [
            //     'attribute' => 'last_login_ip',
            //     'value' => function ($model) {
            //         return $model->last_login_ip == null
            //             ? '<span class="not-set">' . Yii::t('usuario', '(not set)') . '</span>'
            //             : $model->last_login_ip;
            //     },
            //     'format' => 'html',
            // ],
            [
                'header' => Yii::t('usuario', 'Confirmation'),
                'value' => function ($model) {
                    if ($model->isConfirmed) {
                        return '<div class="text-center">
                                <span class="text-success">' . Yii::t('usuario', 'Confirmed') . '</span>
                            </div>';
                    }

                    return Html::a(
                        Yii::t('usuario', 'Confirm'),
                        ['confirm', 'id' => $model->id],
                        [
                            'class' => 'btn btn-xs btn-success',
                            'data-method' => 'post',
                            'data-confirm' => Yii::t('usuario', 'Are you sure you want to confirm this user?'),
                        ]
                    );                },
                'format' => 'raw',
                'contentOptions' => ['class' => 'text-center'],
                'visible' => Yii::$app->getModule('user')->enableEmailConfirmation,
            ],
            //'password_age',
            [
                'header' => Yii::t('usuario', 'Block status'),
                'value' => function ($model) {
                    if ($model->isBlocked) {
                        return Html::a(
                            Yii::t('usuario', 'Unblock'),
                            ['block', 'id' => $model->id],
                            [
                                'class' => 'btn btn-xs btn-success btn-block',
                                'data-method' => 'post',
                                'data-confirm' => Yii::t('usuario', 'Are you sure you want to unblock this user?'),
                            ]
                        );
                    }

                    return Html::a(
                        Yii::t('usuario', 'Block'),
                        ['block', 'id' => $model->id],
                        [
                            'class' => 'btn btn-xs btn-danger btn-block',
                            'data-method' => 'post',
                            'data-confirm' => Yii::t('usuario', 'Are you sure you want to block this user?'),
                        ]
                    );
                },
                'format' => 'raw',
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {switch} {reset} {force-password-change} {update} {delete}',
                'buttons' => [  
                    'switch' => function ($url, $model) use ($module) {
                        if ($model->id != Yii::$app->user->id && $module->enableSwitchIdentities) {
                            return Html::a(
                                '<span class="glyphicon glyphicon-user"></span>',
                                ['/user/admin/switch-identity', 'id' => $model->id],
                                [
                                    'title' => Yii::t('usuario', 'Impersonate this user'),
                                    'data-confirm' => Yii::t(
                                        'usuario',
                                        'Are you sure you want to switch to this user for the rest of this Session?'
                                    ),
                                    'data-method' => 'POST',
                                ]
                            );
                        }

                        return null;
                    },
                    'reset' => function ($url, $model) use ($module) {
                        if($module->allowAdminPasswordRecovery) {
                            return Html::a(
                                '<span class="glyphicon glyphicon-flash"></span>',
                                ['/user/admin/password-reset', 'id' => $model->id],
                                [
                                    'title' => Yii::t('usuario', 'Send password recovery email'),
                                    'data-confirm' => Yii::t(
                                        'usuario',
                                        'Are you sure you wish to send a password recovery email to this user?'
                                    ),
                                    'data-method' => 'POST',
                                ]
                            );
                        }

                        return null;
                    },                    'force-password-change' => function ($url, $model) use ($module) {
                        if (is_null($module->maxPasswordAge)) {
                            return null;
                        }
                        return Html::a(
                            '<span class="glyphicon glyphicon-time"></span>',
                            ['/user/admin/force-password-change', 'id' => $model->id],
                            [
                                'title' => Yii::t('usuario', 'Force password change at next login'),
                                'data-confirm' => Yii::t(
                                    'usuario',
                                    'Are you sure you wish the user to change their password at next login?'
                                ),
                                'data-method' => 'POST',
                            ]
                        );
                    },
                ]
            ],
        ],
    ]
); ?>
</div>
<?php Pjax::end() ?>

<?php
// Modal personalizado para confirmar eliminación de usuario
use yii\bootstrap5\Modal;

Modal::begin([
    'id' => 'confirmDeleteModal',
    'title' => 'Eliminar Usuario',
    'options' => [
        'tabindex' => '-1',
        'data-bs-backdrop' => 'static', // Evitar cerrar al hacer clic fuera
        'data-bs-keyboard' => 'false', // Evitar cerrar con ESC por accidente
        'style' => 'z-index: 9999 !important;', // Forzar z-index alto
    ],
    'dialogOptions' => [
        'class' => 'modal-dialog modal-dialog-centered',
    ],
]);
?>
<p><strong>¿Está seguro que desea eliminar este usuario?</strong></p>
<div class="alert alert-warning">
    <p><strong>Email:</strong> <span id="modalUserEmail"></span></p>
</div>
<div class="alert alert-danger mb-3">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <strong>¡Atención!</strong> Esta acción no se puede deshacer.
</div>
<div class="d-flex justify-content-end gap-3">
    <?= \yii\helpers\Html::button('Cancelar', [
        'class' => 'btn btn-secondary',
        'data-bs-dismiss' => 'modal'
    ]) ?>
    <?= \yii\helpers\Html::button('Eliminar Usuario', [
        'class' => 'btn btn-danger',
        'id' => 'confirmDelete'
    ]) ?>
</div>
<?php
Modal::end();
?>

<script>
// Override inmediato de window.confirm para interceptar confirmaciones de eliminación
(function() {
    const originalConfirm = window.confirm;
    let pendingDeleteData = null;
    
    window.confirm = function(message) {
        // Si estamos en proceso de eliminación, permitir el confirm original
        if (window.deletionInProgress) {
            return originalConfirm.call(this, message);
        }
        
        if (message && message.includes('eliminar')) {
            // Encontrar el enlace activo
            const activeElement = document.activeElement;
            if (activeElement && activeElement.tagName === 'A') {
                const row = activeElement.closest('tr');
                if (row) {
                    const cells = row.querySelectorAll('td');
                    const userName = cells[0] ? cells[0].textContent.trim() : 'N/A';
                    const userEmail = cells[1] ? cells[1].textContent.trim() : 'N/A';
                    
                    // Guardar datos para cuando jQuery esté disponible
                    pendingDeleteData = {
                        userName: userName,
                        userEmail: userEmail,
                        link: activeElement
                    };
                    
                    // Intentar mostrar el modal si jQuery ya está disponible
                    if (typeof $ !== 'undefined') {
                        $('#modalUserName').text(userName);
                        $('#modalUserEmail').text(userEmail);
                        $('#confirmDeleteModal').modal('show');
                    } else {
                        // Si jQuery no está disponible, esperar un poco y intentar de nuevo
                        setTimeout(function() {
                            if (typeof $ !== 'undefined' && pendingDeleteData) {
                                $('#modalUserName').text(pendingDeleteData.userName);
                                $('#modalUserEmail').text(pendingDeleteData.userEmail);
                                $('#confirmDeleteModal').modal('show');
                            }
                        }, 100);
                    }
                    
                    return false; // Cancelar el confirm nativo
                }
            }
        }
        
        // Para otros mensajes, usar el confirm original
        return originalConfirm.call(this, message);
    };
    
    // Hacer que esta función esté disponible globalmente para referencia
    window.originalConfirm = originalConfirm;
    window.pendingDeleteData = pendingDeleteData;
})();

// Esperar a que jQuery esté disponible
function waitForJQuery() {
    if (typeof $ !== 'undefined') {
        initializeModal();
    } else {
        setTimeout(waitForJQuery, 100);
    }
}

function initializeModal() {
    $(document).ready(function() {
        // Configurar variables
        const originalConfirm = window.confirm;
        let deleteForm = null;    // Interceptar clics en enlaces de eliminación usando addEventListener nativo con captura
    document.addEventListener('click', function(e) {
        // Salir si ya estamos en proceso de eliminación para evitar bucles
        if (window.deletionInProgress) {
            return;
        }
        
        const target = e.target;
        const link = target.closest('a[data-confirm]');
        
        if (link) {
            const dataConfirm = link.getAttribute('data-confirm');
            
            if (dataConfirm && dataConfirm.includes('eliminar')) {
                // Prevenir comportamiento por defecto
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                
                const $link = $(link);
                const $row = $link.closest('tr');
                const userName = $row.find('td:first').text().trim();
                const userEmail = $row.find('td:nth-child(2)').text().trim();
                
                // Guardar el enlace
                deleteForm = $link;
                
                // Actualizar y mostrar el modal
                $('#modalUserName').text(userName || 'N/A');
                $('#modalUserEmail').text(userEmail || 'N/A');
                $('#confirmDeleteModal').modal('show');
                
                return false;
            }
        }
    }, true); // TRUE = usar fase de captura

    // Funcionalidad del paginador
    function adjustPaginationPosition() {
        const paginationContainer = $('.pagination-container');
        if (paginationContainer.length) {
            if (window.innerWidth <= 768) {
                paginationContainer.css('bottom', '10px');
            } else {
                paginationContainer.css('bottom', '0');
            }
        }
    }
    
    adjustPaginationPosition();
    $(window).resize(adjustPaginationPosition);
    
    $(document).on('click', '.pagination-container .pagination a', function() {
        setTimeout(function() {
            $('.fixed-table-container').animate({ scrollTop: 0 }, 300);
        }, 100);
    });
    
    $('.pagination-container').hover(
        function() { $(this).css('box-shadow', '0 -4px 20px rgba(0,0,0,0.15)'); },
        function() { $(this).css('box-shadow', '0 -2px 10px rgba(0,0,0,0.1)'); }
    );


    $(document).on('click', 'a[data-confirm]', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const $link = $(this);
        const href = $link.attr('href');
        const confirmMessage = $link.data('confirm');
    
        
        // Si es un enlace de eliminar (verificar por URL o mensaje de confirmación)
        if ((href && href.indexOf('delete') !== -1) || 
            (confirmMessage && (confirmMessage.toLowerCase().indexOf('eliminar') !== -1 || 
                              confirmMessage.toLowerCase().indexOf('delete') !== -1))) {
            
            // Obtener información del usuario desde la fila
            const $row = $link.closest('tr');
            const userName = $row.find('td:first').text().trim();
            const userEmail = $row.find('td:nth-child(2)').text().trim();
            
            
            // Actualizar el modal con la información del usuario
            $('#modalUserName').text(userName || 'N/A');
            $('#modalUserEmail').text(userEmail || 'N/A');
            
            // Guardar el enlace para usar después
            deleteForm = $link;
            
            // Mostrar el modal
            $('#confirmDeleteModal').modal('show');
            
            return false;
        } else {
            console.log('❌ Not a delete link, letting it proceed normally');
        }
    });
    
    // Confirmar eliminación
    $('#confirmDelete').click(function() {
        if (deleteForm) {
            // Cerrar el modal
            $('#confirmDeleteModal').modal('hide');
            
            // Hacer la petición HTTP directamente
            const deleteUrl = deleteForm.attr('href');
            const method = deleteForm.data('method') || 'POST';
            
            // Crear formulario oculto para enviar la petición
            const $form = $('<form>', {
                method: 'POST',
                action: deleteUrl,
                style: 'display: none;'
            });
            
            // Agregar token CSRF de Yii2
            const csrfParam = $('meta[name="csrf-param"]').attr('content') || '_csrf-frontend';
            const csrfToken = $('meta[name="csrf-token"]').attr('content');
            
            if (csrfToken) {
                $form.append($('<input>', {
                    type: 'hidden',
                    name: csrfParam,
                    value: csrfToken
                }));
            } else {
                // Buscar token CSRF en formularios existentes
                const existingCsrf = $('input[name*="csrf"]').first();
                if (existingCsrf.length) {
                    $form.append($('<input>', {
                        type: 'hidden',
                        name: existingCsrf.attr('name'),
                        value: existingCsrf.val()
                    }));
                }
            }
            
            // Si el método no es POST, agregar campo _method
            if (method.toUpperCase() !== 'POST') {
                $form.append($('<input>', {
                    type: 'hidden',
                    name: '_method',
                    value: method.toUpperCase()
                }));
            }
            
            // Agregar al DOM y enviar
            $('body').append($form);
            $form.submit();
        }
    });
    
    // Limpiar al cerrar el modal
    $('#confirmDeleteModal').on('hidden.bs.modal', function() {
        deleteForm = null;
    });
    
    }); // Fin de $(document).ready()
}

// Inicializar cuando jQuery esté disponible
waitForJQuery();
</script>

<?php $this->endContent() ?>
