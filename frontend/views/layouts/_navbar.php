<?php

use yii\helpers\Url;
use yii\bootstrap5\Html;
use rmrevin\yii\fontawesome\FAS;

// Agregar estilo para el dropdown del navbar
$this->registerCss("
.navbar-dropdown .dropdown-menu {
    z-index: 10000 !important;
}
.navbar-dropdown.show {
    z-index: 10000 !important;
}
.dropdown-close-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    background: transparent;
    border: none;
    font-size: 20px;
    line-height: 1;
    color: #6c757d;
    cursor: pointer;
    padding: 0;
    width: 25px;
    height: 25px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    transition: all 0.2s;
}
.dropdown-close-btn:hover {
    background-color: #f8f9fa;
    color: #dc3545;
}
.navbar-dropdown .dropdown-menu {
    position: relative;
    padding-top: 35px;
}
");

?>
<nav
    class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
    id="layout-navbar"
>
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
            <i class="bx bx-menu bx-sm"></i>
        </a>
    </div>

    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
        <!-- Search -->
        <div class="navbar-nav align-items-center">
            <div class="nav-item d-flex align-items-center" style="font-weight: bold; font-size: 22px">
                <?= $this->title ?>
            </div>
        </div>
        <!-- /Search -->

        <ul class="navbar-nav flex-row align-items-center ms-auto">
            <!-- Place this tag where you want the button to render. -->


            <!-- User -->
            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                        <img src="https://ui-avatars.com/api/?name=<?= 'Administrator' ?>" alt
                             class="w-px-40 h-auto rounded-circle"/>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <!-- Botón de cerrar -->
                    <button type="button" class="dropdown-close-btn" id="closeDropdown" aria-label="Cerrar">
                        <i class="bx bx-x"></i>
                    </button>
                    
                    <li>
                        <a class="dropdown-item" href="#">
                            <div class="d-flex">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar avatar-online">
                                        <img src="https://ui-avatars.com/api/?name=<?= 'Administrator' ?>" alt
                                             class="w-px-40 h-auto rounded-circle"/>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <span class="fw-semibold d-block">
                                        <?= 'Administrator' ?>
                                    </span>
                                </div>
                            </div>
                        </a>
                    </li>
<!--                    <li>-->
<!--                        <div class="dropdown-divider"></div>-->
<!--                    </li>-->
<!--                    <li>-->
<!--                        <a class="dropdown-item" href="#">-->
<!--                            <i class="bx bx-user me-2"></i>-->
<!--                            <span class="align-middle">My Profile</span>-->
<!--                        </a>-->
<!--                    </li>-->
<!--                    <li>-->
<!--                        <a class="dropdown-item" href="#">-->
<!--                            <i class="bx bx-cog me-2"></i>-->
<!--                            <span class="align-middle">Settings</span>-->
<!--                        </a>-->
<!--                    </li>-->
<!--                    <li>-->
<!--                        <a class="dropdown-item" href="#">-->
<!--                        <span class="d-flex align-items-center align-middle">-->
<!--                          <i class="flex-shrink-0 bx bx-credit-card me-2"></i>-->
<!--                          <span class="flex-grow-1 align-middle">Billing</span>-->
<!--                          <span class="flex-shrink-0 badge badge-center rounded-pill bg-danger w-px-20 h-px-20">4</span>-->
<!--                        </span>-->
<!--                        </a>-->
<!--                    </li>-->
                    <li>
                        <div class="dropdown-divider"></div>
                    </li>
                    <li>
                        <?=
                        Html::a(
                            "<i class='bx bx-power-off me-2'></i>
                            <span class='align-middle'>".Yii::t('app', 'Logout')."</span>",
                            Url::to(['//user/security/logout']), [
                            'class' => 'dropdown-item',
                            'data' => [
                                'method' => 'post'
                            ]
                        ])
                        ?>
                    </li>
                </ul>
            </li>
            <!--/ User -->
        </ul>
    </div>
</nav>

<?php
// Script para reinicializar dropdowns de Bootstrap después de Pjax
$this->registerJs("
// Función para inicializar todos los dropdowns
function initDropdowns() {
    // Verificar si Bootstrap está disponible
    if (typeof bootstrap !== 'undefined' && bootstrap.Dropdown) {
        var dropdownElementList = [].slice.call(document.querySelectorAll('[data-bs-toggle=\"dropdown\"]'));
        
        dropdownElementList.map(function (dropdownToggleEl) {
            
            // Destruir instancia previa si existe para evitar duplicados
            var existingInstance = bootstrap.Dropdown.getInstance(dropdownToggleEl);
            if (existingInstance) {
                existingInstance.dispose();
            }
            
            // Crear nueva instancia
            var newInstance = new bootstrap.Dropdown(dropdownToggleEl, {
                autoClose: true
            });
            
            // Remover listeners previos para evitar duplicados
            dropdownToggleEl.removeEventListener('click', dropdownToggleEl._customClickHandler);
            
            // Agregar listener manual con capture para ejecutarse ANTES que otros listeners
            dropdownToggleEl._customClickHandler = function(e) {
                e.stopPropagation();
                e.stopImmediatePropagation();
                
                var instance = bootstrap.Dropdown.getInstance(dropdownToggleEl);
                if (instance) {
                    instance.toggle();
                    
                    // Verificar si se aplicó la clase 'show'
                    setTimeout(function() {
                        var menu = dropdownToggleEl.nextElementSibling;
                        var parent = dropdownToggleEl.closest('.dropdown');
                        // Si no se mostró, intentar manualmente
                        if (menu && !menu.classList.contains('show')) {
                            menu.classList.add('show');
                            parent.classList.add('show');
                            menu.setAttribute('data-bs-popper', 'none');
                        }
                    }, 50);
                } else {
                    console.error('No se encontró instancia de dropdown');
                }
            };
            
            // Usar capture: true para ejecutarse antes que los listeners en bubbling
            dropdownToggleEl.addEventListener('click', dropdownToggleEl._customClickHandler, true);
            
            return newInstance;
        });
    } else {
        console.error('Bootstrap o Bootstrap.Dropdown NO está disponible!');
    }
}

// Inicializar al cargar la página
initDropdowns();

// Reinicializar después de cada request de Pjax
$(document).on('pjax:end', function() {
    initDropdowns();
});

// Manejar el botón de cerrar del dropdown
$(document).on('click', '#closeDropdown', function(e) {
    e.preventDefault();
    e.stopPropagation();
    
    var dropdownMenu = $(this).closest('.dropdown-menu');
    var dropdownParent = dropdownMenu.closest('.dropdown');
    
    dropdownMenu.removeClass('show');
    dropdownParent.removeClass('show');
});

", \yii\web\View::POS_READY);
?>
