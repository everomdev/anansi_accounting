<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;
use common\widgets\Alert;

\backend\assets\SneatAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">

<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?>
    </title>
    <?php $this->head() ?>
</head>

<body>
<?php $this->beginBody() ?>

<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        <?= $this->render('_aside') ?>
        <div class="layout-page">
            <?= $this->render('_navbar') ?>
            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">
                    <?= \backend\widgets\FlashMessages::widget(); ?>
                    <?= $content ?>
                </div>
                <?= $this->render('_footer') ?>
                <div class="content-backdrop fade"></div>
            </div>
        </div>
    </div>
    <div class="layout-overlay layout-menu-toggle"></div>
</div>
<?php if (Yii::$app->session->hasFlash('backup-reminder-modal')): ?>
<div class="modal fade" id="backupReminderModal" tabindex="-1" aria-labelledby="backupReminderLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="backupReminderLabel">Recordatorio de Seguridad</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?= Yii::$app->session->getFlash('backup-reminder-modal') ?>
                
                <!-- Botones de acción -->
                <div class="text-center mt-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <a href="<?= \yii\helpers\Url::to(['/standard-recipe/export-recipes-to-excel', 'all' => 'true', 'type' => 'main']) ?>" class="btn btn-outline-success w-100">
                                <i class="fas fa-download me-2"></i> Descargar Recetas
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="<?= \yii\helpers\Url::to(['/standard-recipe/export-recipes-to-excel', 'all' => 'true', 'type' => 'sub']) ?>" class="btn btn-outline-info w-100">
                                <i class="fas fa-download me-2"></i> Descargar Sub-recetas
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="<?= \yii\helpers\Url::to(['/ingredient-stock/export-ingredients']) ?>" class="btn btn-outline-primary w-100">
                                <i class="fas fa-download me-2"></i> Descargar Insumos
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Crear una nueva instancia del modal con Bootstrap 5
    var backupModal = new bootstrap.Modal(document.getElementById('backupReminderModal'));
    backupModal.show();
});
</script>
<?php endif; ?>
<?php $this->endBody() ?>
</body>

</html>
<style>
    @media (min-width: 992px) {
    #layout-menu {
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        width: 260px;
        overflow-y: auto;
        z-index: 1050;
        box-shadow: 0 2px 6px rgba(67, 89, 113, 0.2);
    }
    
    .layout-page {
        margin-left: 260px;
    }
}

/* Estilos para móvil */
@media (max-width: 991.98px) {
    #layout-menu {
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        width: 260px;
        z-index: 1090;
        transform: translateX(-100%);
        transition: transform 0.3s, box-shadow 0.3s;
    }
    
    .layout-menu-expanded #layout-menu {
        transform: translateX(0);
        box-shadow: 0 0 10px rgba(0,0,0,0.3);
        z-index: 1100 !important; /* Asegurar que esté por encima del overlay */
    }
    
    /* Ajustar el overlay */
    .layout-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1089; /* Justo por debajo del menú */
        background-color: rgba(0,0,0,0.5);
        display: none;
    }
    
    .layout-menu-expanded .layout-overlay {
        display: block;
    }
    
    .layout-page {
        margin-left: 0 !important;
    }
}

/* Ocultar scrollbar pero permitir scroll */
#layout-menu {
    scrollbar-width: thin;
    -ms-overflow-style: none;
}

#layout-menu::-webkit-scrollbar {
    width: 4px;
}

#layout-menu::-webkit-scrollbar-thumb {
    background-color: rgba(0,0,0,0.2);
    border-radius: 4px;
}

/* Estilos adicionales para resolver problemas de interacción */
.layout-menu-expanded {
    overflow: hidden; /* Evitar scroll del body cuando el menú está abierto */
}

/* Arreglo para garantizar que los elementos del menú sean interactivos */
.menu-inner, .menu-link, .menu-item {
    position: relative;
    z-index: 1;
}
@media (max-width: 991.98px) {
    /* Asegurar que el menú tenga su propio contexto de scroll */
    #layout-menu {
        overflow-y: auto;
        -webkit-overflow-scrolling: touch; /* Para mejorar el scroll en iOS */
    }
    
    /* Evitar que el contenido principal se desplace cuando el menú está abierto */
    body.layout-menu-expanded .layout-page {
        position: fixed;
        width: 100%;
        height: 100%;
        overflow: hidden;
    }
    
    /* Asegurar que el overlay cubra toda la página */
    .layout-overlay {
        -webkit-backdrop-filter: blur(2px);
        backdrop-filter: blur(2px);
    }
}
</style>

<?php
// Mejora para el comportamiento del menú móvil
$js = <<<JS
document.addEventListener('DOMContentLoaded', function() {
    // Referencias a elementos clave
    var layoutMenuToggler = document.querySelector('.layout-menu-toggle');
    var layoutOverlay = document.querySelector('.layout-overlay');
    var body = document.querySelector('body');
    var layoutMenu = document.getElementById('layout-menu');
    var lastScrollTop = 0;
    
    // SOLUCIÓN PARA DESKTOP: Evitar que el scroll del menú afecte al contenido principal
    if (layoutMenu) {
        // Mejorado para funcionar en todos los navegadores y plataformas
        layoutMenu.addEventListener('mouseover', function() {
            // Solo aplicar en desktop
            if (window.innerWidth >= 992) {
                // Guardar la posición actual del scroll
                lastScrollTop = window.pageYOffset || document.documentElement.scrollTop;
                
                // Bloquear el scroll del documento principal
                document.body.style.overflow = 'hidden';
                document.documentElement.style.overflow = 'hidden';
                document.body.style.position = 'fixed';
                document.body.style.width = '100%';
                document.body.style.top = -lastScrollTop + 'px';
            }
        });
        
        layoutMenu.addEventListener('mouseout', function() {
            // Solo aplicar en desktop
            if (window.innerWidth >= 992) {
                // Restaurar el scroll del documento principal
                document.body.style.overflow = '';
                document.documentElement.style.overflow = '';
                document.body.style.position = '';
                document.body.style.width = '';
                document.body.style.top = '';
                
                // Restaurar la posición anterior del scroll
                window.scrollTo(0, lastScrollTop);
            }
        });
        
        // Interceptar eventos de wheel específicamente en el menú
        layoutMenu.addEventListener('wheel', function(e) {
            if (window.innerWidth >= 992) {
                e.stopPropagation();
            }
        }, { passive: false });
    }
    
    // Resto del código para móvil (mantener lo que ya tienes)
    // Evitar que el scroll del menú se propague al contenido principal en móvil
    if (layoutMenu) {
        // Prevenir que el evento de scroll se propague fuera del menú en móvil
        layoutMenu.addEventListener('wheel', function(e) {
            // Si estamos en móvil y el menú está expandido
            if (window.innerWidth < 992 && body.classList.contains('layout-menu-expanded')) {
                // Verificar si el scroll llegó al límite
                const scrollTop = this.scrollTop;
                const scrollHeight = this.scrollHeight;
                const height = this.clientHeight;
                
                // Si intenta hacer scroll hacia arriba en el límite superior
                if ((scrollTop === 0 && e.deltaY < 0) || 
                    // O intenta hacer scroll hacia abajo en el límite inferior
                    (scrollTop + height >= scrollHeight && e.deltaY > 0)) {
                    // No hacer nada, permitir el comportamiento natural
                } else {
                    // Sino, prevenir que el evento se propague
                    e.stopPropagation();
                }
            }
        }, { passive: false });
        
        // También evitar que el touch se propague (para dispositivos táctiles)
        layoutMenu.addEventListener('touchmove', function(e) {
            if (window.innerWidth < 992 && body.classList.contains('layout-menu-expanded')) {
                e.stopPropagation();
            }
        }, { passive: false });
    }
    
    // Controlar el clic en el botón de hamburguesa
    if (layoutMenuToggler) {
        layoutMenuToggler.addEventListener('click', function(e) {
            e.preventDefault();
            body.classList.toggle('layout-menu-expanded');
            
            // Si el menú está expandido, asegurarse de que sea interactivo
            if (body.classList.contains('layout-menu-expanded')) {
                layoutMenu.style.zIndex = '1100'; // Mayor que el overlay
                
                // Bloquear scroll del body cuando el menú está abierto en móvil
                if (window.innerWidth < 992) {
                    document.body.style.overflow = 'hidden';
                }
            } else {
                // Restaurar z-index original y permitir scroll del body cuando se cierra
                setTimeout(function() {
                    layoutMenu.style.zIndex = '1090';
                    document.body.style.overflow = '';
                }, 300); // Esperar a que termine la transición
            }
        });
    }
    
    // Cerrar menú al hacer clic en el overlay
    if (layoutOverlay) {
        layoutOverlay.addEventListener('click', function(e) {
            e.preventDefault();
            body.classList.remove('layout-menu-expanded');
            // Restaurar z-index original y permitir scroll del body cuando se cierra
            setTimeout(function() {
                layoutMenu.style.zIndex = '1090';
                document.body.style.overflow = '';
            }, 300);
        });
    }
    
    // Cerrar menú al hacer clic en cualquier elemento del menú en móvil
    var menuLinks = document.querySelectorAll('#layout-menu .menu-link');
    menuLinks.forEach(function(link) {
        link.addEventListener('click', function() {
            if (window.innerWidth < 992) {
                body.classList.remove('layout-menu-expanded');
                // Restaurar z-index original cuando se cierra
                setTimeout(function() {
                    layoutMenu.style.zIndex = '1090';
                    document.body.style.overflow = '';
                }, 300);
            }
        });
    });
    
    // Asegurar que el menú esté visible en escritorio
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 992) {
            layoutMenu.style.transform = '';
            layoutMenu.style.zIndex = '1050'; // z-index para escritorio
            document.body.style.overflow = ''; // Restaurar overflow
            document.documentElement.style.overflow = '';
            document.body.style.position = '';
            document.body.style.width = '';
            document.body.style.top = '';
            
            // Restaurar posición de scroll si es necesario
            if (parseInt(document.body.style.top) !== 0) {
                window.scrollTo(0, lastScrollTop);
            }
        }
    });
});
JS;
$this->registerJs($js);
?>

<?php $this->endPage() ?>