<?php

namespace console\controllers;

use backend\rules\PaymentRule;
use yii\console\Controller;

class RbacController extends Controller
{
    public function actionInit()
    {
        $authManager = \Yii::$app->authManager;

        // Helper function to safely add child
        $addChildSafely = function($parent, $child) use ($authManager) {
            try {
                $authManager->addChild($parent, $child);
            } catch (\yii\db\IntegrityException $e) {
                // Ignore if already exists
            }
        };

        // Remove only if you want to reset everything, but for now, preserve existing assignments
        // $authManager->removeAll();

        $paymentRule = new PaymentRule();
        $existingRule = $authManager->getRule($paymentRule->name);
        if (!$existingRule) {
            $authManager->add($paymentRule);
        }
        // create roles
        // Role Admin
        $roleAdmin = $authManager->getRole('admin');
        if (!$roleAdmin) {
            $roleAdmin = $authManager->createRole('admin');
            $roleAdmin->description = "Administrador del sistema";
            $authManager->add($roleAdmin);
        }

        // Role Storage
        $roleStorage = $authManager->getRole('storage');
        if (!$roleStorage) {
            $roleStorage = $authManager->createRole('storage');
            $roleStorage->description = "Almacén";
            $authManager->add($roleStorage);
        }

        // Role Storage Admin
        $roleStorageAdmin = $authManager->getRole('storage_admin');
        if (!$roleStorageAdmin) {
            $roleStorageAdmin = $authManager->createRole('storage_admin');
            $roleStorageAdmin->description = "Administrador de Almacén";
            $authManager->add($roleStorageAdmin);
        }

        // Role Chef
        $roleChef = $authManager->getRole('chef');
        if (!$roleChef) {
            $roleChef = $authManager->createRole('chef');
            $roleChef->description = "Chef";
            $authManager->add($roleChef);
        }

        // Role Executive Chef
        $roleExecutiveChef = $authManager->getRole('executive_chef');
        if (!$roleExecutiveChef) {
            $roleExecutiveChef = $authManager->createRole('executive_chef');
            $roleExecutiveChef->description = "Chef Ejecutivo";
            $authManager->add($roleExecutiveChef);
        }

        // Role Owner
        $roleOwner = $authManager->getRole('owner');
        if (!$roleOwner) {
            $roleOwner = $authManager->createRole('owner');
            $roleOwner->description = "Propietario";
            $authManager->add($roleOwner);
        }

        // Role Administrator
        $roleAdministrator = $authManager->getRole('administrator');
        if (!$roleAdministrator) {
            $roleAdministrator = $authManager->createRole('administrator');
            $roleAdministrator->description = "Administrador";
            $authManager->add($roleAdministrator);
        }

        // Create permissions
        // Storage permissions
        $permissionStorageList = $authManager->getPermission('storage_list');
        if (!$permissionStorageList) {
            $permissionStorageList = $authManager->createPermission('storage_list');
            $permissionStorageList->description = "Ver almacén";
            $permissionStorageList->ruleName = $paymentRule->name;
            $authManager->add($permissionStorageList);
        }
        $permissionStorageView = $authManager->getPermission('storage_view');
        if (!$permissionStorageView) {
            $permissionStorageView = $authManager->createPermission('storage_view');
            $permissionStorageView->description = "Ver detalles de almacén";
            $permissionStorageView->ruleName = $paymentRule->name;
            $authManager->add($permissionStorageView);
        }
//        $authManager->add($permissionStorageCreate);
//        $authManager->add($permissionStorageUpdate);
//        $authManager->add($permissionStorageDelete);

        // Subrecipe permissions
        $permissionSubrecipeList = $authManager->getPermission('subrecipe_list');
        if (!$permissionSubrecipeList) {
            $permissionSubrecipeList = $authManager->createPermission('subrecipe_list');
            $permissionSubrecipeList->description = "Ver lista de subrecetas";
            $permissionSubrecipeList->ruleName = $paymentRule->name;
            $authManager->add($permissionSubrecipeList);
        }
        $permissionSubrecipeView = $authManager->getPermission('subrecipe_view');
        if (!$permissionSubrecipeView) {
            $permissionSubrecipeView = $authManager->createPermission('subrecipe_view');
            $permissionSubrecipeView->description = "Ver detalles de subreceta";
            $permissionSubrecipeView->ruleName = $paymentRule->name;
            $authManager->add($permissionSubrecipeView);
        }
        $permissionSubrecipeCreate = $authManager->getPermission('subrecipe_create');
        if (!$permissionSubrecipeCreate) {
            $permissionSubrecipeCreate = $authManager->createPermission('subrecipe_create');
            $permissionSubrecipeCreate->description = "Crear una nueva subreceta";
            $permissionSubrecipeCreate->ruleName = $paymentRule->name;
            $authManager->add($permissionSubrecipeCreate);
        }
        $permissionSubrecipeUpdate = $authManager->getPermission('subrecipe_update');
        if (!$permissionSubrecipeUpdate) {
            $permissionSubrecipeUpdate = $authManager->createPermission('subrecipe_update');
            $permissionSubrecipeUpdate->description = "Actualizar información de subreceta";
            $permissionSubrecipeUpdate->ruleName = $paymentRule->name;
            $authManager->add($permissionSubrecipeUpdate);
        }
        $permissionSubrecipeDelete = $authManager->getPermission('subrecipe_delete');
        if (!$permissionSubrecipeDelete) {
            $permissionSubrecipeDelete = $authManager->createPermission('subrecipe_delete');
            $permissionSubrecipeDelete->description = "Eliminar una subreceta";
            $permissionSubrecipeDelete->ruleName = $paymentRule->name;
            $authManager->add($permissionSubrecipeDelete);
        }

        // Recipe permissions
        $permissionRecipeList = $authManager->getPermission('recipe_list');
        if (!$permissionRecipeList) {
            $permissionRecipeList = $authManager->createPermission('recipe_list');
            $permissionRecipeList->description = "Ver lista de recetas";
            $permissionRecipeList->ruleName = $paymentRule->name;
            $authManager->add($permissionRecipeList);
        }
        $permissionRecipeView = $authManager->getPermission('recipe_view');
        if (!$permissionRecipeView) {
            $permissionRecipeView = $authManager->createPermission('recipe_view');
            $permissionRecipeView->description = "Ver detalles de receta";
            $permissionRecipeView->ruleName = $paymentRule->name;
            $authManager->add($permissionRecipeView);
        }
        $permissionRecipeCreate = $authManager->getPermission('recipe_create');
        if (!$permissionRecipeCreate) {
            $permissionRecipeCreate = $authManager->createPermission('recipe_create');
            $permissionRecipeCreate->description = "Crear una nueva receta";
            $permissionRecipeCreate->ruleName = $paymentRule->name;
            $authManager->add($permissionRecipeCreate);
        }
        $permissionRecipeUpdate = $authManager->getPermission('recipe_update');
        if (!$permissionRecipeUpdate) {
            $permissionRecipeUpdate = $authManager->createPermission('recipe_update');
            $permissionRecipeUpdate->description = "Actualizar información de receta";
            $permissionRecipeUpdate->ruleName = $paymentRule->name;
            $authManager->add($permissionRecipeUpdate);
        }
        $permissionRecipeDelete = $authManager->getPermission('recipe_delete');
        if (!$permissionRecipeDelete) {
            $permissionRecipeDelete = $authManager->createPermission('recipe_delete');
            $permissionRecipeDelete->description = "Eliminar una receta";
            $permissionRecipeDelete->ruleName = $paymentRule->name;
            $authManager->add($permissionRecipeDelete);
        }

        // Convoy permissions
        $permissionConvoyList = $authManager->getPermission('convoy_list');
        if (!$permissionConvoyList) {
            $permissionConvoyList = $authManager->createPermission('convoy_list');
            $permissionConvoyList->description = "Ver lista de convoyes";
            $permissionConvoyList->ruleName = $paymentRule->name;
            $authManager->add($permissionConvoyList);
        }
        $permissionConvoyView = $authManager->getPermission('convoy_view');
        if (!$permissionConvoyView) {
            $permissionConvoyView = $authManager->createPermission('convoy_view');
            $permissionConvoyView->description = "Ver detalles de convoy";
            $permissionConvoyView->ruleName = $paymentRule->name;
            $authManager->add($permissionConvoyView);
        }
        $permissionConvoyCreate = $authManager->getPermission('convoy_create');
        if (!$permissionConvoyCreate) {
            $permissionConvoyCreate = $authManager->createPermission('convoy_create');
            $permissionConvoyCreate->description = "Crear un nuevo convoy";
            $permissionConvoyCreate->ruleName = $paymentRule->name;
            $authManager->add($permissionConvoyCreate);
        }
        $permissionConvoyUpdate = $authManager->getPermission('convoy_update');
        if (!$permissionConvoyUpdate) {
            $permissionConvoyUpdate = $authManager->createPermission('convoy_update');
            $permissionConvoyUpdate->description = "Actualizar información de convoy";
            $permissionConvoyUpdate->ruleName = $paymentRule->name;
            $authManager->add($permissionConvoyUpdate);
        }
        $permissionConvoyDelete = $authManager->getPermission('convoy_delete');
        if (!$permissionConvoyDelete) {
            $permissionConvoyDelete = $authManager->createPermission('convoy_delete');
            $permissionConvoyDelete->description = "Eliminar un convoy";
            $permissionConvoyDelete->ruleName = $paymentRule->name;
            $authManager->add($permissionConvoyDelete);
        }

        // Combo permissions
        $permissionComboList = $authManager->getPermission('combo_list');
        if (!$permissionComboList) {
            $permissionComboList = $authManager->createPermission('combo_list');
            $permissionComboList->description = "Ver lista de combos";
            $permissionComboList->ruleName = $paymentRule->name;
            $authManager->add($permissionComboList);
        }
        $permissionComboView = $authManager->getPermission('combo_view');
        if (!$permissionComboView) {
            $permissionComboView = $authManager->createPermission('combo_view');
            $permissionComboView->description = "Ver detalles de combo";
            $permissionComboView->ruleName = $paymentRule->name;
            $authManager->add($permissionComboView);
        }
        $permissionComboCreate = $authManager->getPermission('combo_create');
        if (!$permissionComboCreate) {
            $permissionComboCreate = $authManager->createPermission('combo_create');
            $permissionComboCreate->description = "Crear un nuevo combo";
            $permissionComboCreate->ruleName = $paymentRule->name;
            $authManager->add($permissionComboCreate);
        }
        $permissionComboUpdate = $authManager->getPermission('combo_update');
        if (!$permissionComboUpdate) {
            $permissionComboUpdate = $authManager->createPermission('combo_update');
            $permissionComboUpdate->description = "Actualizar información de combo";
            $permissionComboUpdate->ruleName = $paymentRule->name;
            $authManager->add($permissionComboUpdate);
        }
        $permissionComboDelete = $authManager->getPermission('combo_delete');
        if (!$permissionComboDelete) {
            $permissionComboDelete = $authManager->createPermission('combo_delete');
            $permissionComboDelete->description = "Eliminar un combo";
            $permissionComboDelete->ruleName = $paymentRule->name;
            $authManager->add($permissionComboDelete);
        }

        // Price trend permissions
        $permissionPriceTrendView = $authManager->getPermission('price_trend_view');
        if (!$permissionPriceTrendView) {
            $permissionPriceTrendView = $authManager->createPermission('price_trend_view');
            $permissionPriceTrendView->description = "Ver detalles de tendencia de precios";
            $authManager->add($permissionPriceTrendView);
        }

        // Charts permissions
        $permissionChartsView = $authManager->getPermission('charts_view');
        if (!$permissionChartsView) {
            $permissionChartsView = $authManager->createPermission('charts_view');
            $permissionChartsView->description = "Ver lista de gráficos";
            $permissionChartsView->ruleName = $paymentRule->name;
            $authManager->add($permissionChartsView);
        }

        // Ingredients permissions
        $permissionIngredientsList = $authManager->getPermission('ingredients_list');
        if (!$permissionIngredientsList) {
            $permissionIngredientsList = $authManager->createPermission('ingredients_list');
            $permissionIngredientsList->description = "Ver lista de ingredientes";
            $permissionIngredientsList->ruleName = $paymentRule->name;
            $authManager->add($permissionIngredientsList);
        }
        $permissionIngredientsView = $authManager->getPermission('ingredients_view');
        if (!$permissionIngredientsView) {
            $permissionIngredientsView = $authManager->createPermission('ingredients_view');
            $permissionIngredientsView->description = "Ver detalles de ingredientes";
            $permissionIngredientsView->ruleName = $paymentRule->name;
            $authManager->add($permissionIngredientsView);
        }
        $permissionIngredientsCreate = $authManager->getPermission('ingredients_create');
        if (!$permissionIngredientsCreate) {
            $permissionIngredientsCreate = $authManager->createPermission('ingredients_create');
            $permissionIngredientsCreate->description = "Crear un nuevo ingrediente";
            $permissionIngredientsCreate->ruleName = $paymentRule->name;
            $authManager->add($permissionIngredientsCreate);
        }
        $permissionIngredientsUpdate = $authManager->getPermission('ingredients_update');
        if (!$permissionIngredientsUpdate) {
            $permissionIngredientsUpdate = $authManager->createPermission('ingredients_update');
            $permissionIngredientsUpdate->description = "Actualizar información de ingredientes";
            $permissionIngredientsUpdate->ruleName = $paymentRule->name;
            $authManager->add($permissionIngredientsUpdate);
        }
        $permissionIngredientsDelete = $authManager->getPermission('ingredients_delete');
        if (!$permissionIngredientsDelete) {
            $permissionIngredientsDelete = $authManager->createPermission('ingredients_delete');
            $permissionIngredientsDelete->description = "Eliminar un ingrediente";
            $permissionIngredientsDelete->ruleName = $paymentRule->name;
            $authManager->add($permissionIngredientsDelete);
        }

        // Providers permissions
        $permissionProvidersList = $authManager->getPermission('providers_list');
        if (!$permissionProvidersList) {
            $permissionProvidersList = $authManager->createPermission('providers_list');
            $permissionProvidersList->description = "Ver lista de proveedores";
            $permissionProvidersList->ruleName = $paymentRule->name;
            $authManager->add($permissionProvidersList);
        }
        $permissionProvidersView = $authManager->getPermission('providers_view');
        if (!$permissionProvidersView) {
            $permissionProvidersView = $authManager->createPermission('providers_view');
            $permissionProvidersView->description = "Ver detalles de proveedor";
            $permissionProvidersView->ruleName = $paymentRule->name;
            $authManager->add($permissionProvidersView);
        }
        $permissionProvidersCreate = $authManager->getPermission('providers_create');
        if (!$permissionProvidersCreate) {
            $permissionProvidersCreate = $authManager->createPermission('providers_create');
            $permissionProvidersCreate->description = "Crear un nuevo proveedor";
            $permissionProvidersCreate->ruleName = $paymentRule->name;
            $authManager->add($permissionProvidersCreate);
        }
        $permissionProvidersUpdate = $authManager->getPermission('providers_update');
        if (!$permissionProvidersUpdate) {
            $permissionProvidersUpdate = $authManager->createPermission('providers_update');
            $permissionProvidersUpdate->description = "Actualizar información de proveedor";
            $permissionProvidersUpdate->ruleName = $paymentRule->name;
            $authManager->add($permissionProvidersUpdate);
        }
        $permissionProvidersDelete = $authManager->getPermission('providers_delete');
        if (!$permissionProvidersDelete) {
            $permissionProvidersDelete = $authManager->createPermission('providers_delete');
            $permissionProvidersDelete->description = "Eliminar un proveedor";
            $permissionProvidersDelete->ruleName = $paymentRule->name;
            $authManager->add($permissionProvidersDelete);
        }

        // Menu permissions
        $permissionMenuView = $authManager->getPermission('menu_view');
        if (!$permissionMenuView) {
            $permissionMenuView = $authManager->createPermission('menu_view');
            $permissionMenuView->description = "Ver detalles de menú";
            $permissionMenuView->ruleName = $paymentRule->name;
            $authManager->add($permissionMenuView);
        }
        $permissionMenuUpdate = $authManager->getPermission('menu_update');
        if (!$permissionMenuUpdate) {
            $permissionMenuUpdate = $authManager->createPermission('menu_update');
            $permissionMenuUpdate->description = "Modificar menú";
            $permissionMenuUpdate->ruleName = $paymentRule->name;
            $authManager->add($permissionMenuUpdate);
        }
        $permissionMenuAddItem = $authManager->getPermission('menu_add_item');
        if (!$permissionMenuAddItem) {
            $permissionMenuAddItem = $authManager->createPermission('menu_add_item');
            $permissionMenuAddItem->description = "Agregar receta o combo a un menú";
            $permissionMenuAddItem->ruleName = $paymentRule->name;
            $authManager->add($permissionMenuAddItem);
        }
        $permissionMenuRemoveItem = $authManager->getPermission('menu_remove_item');
        if (!$permissionMenuRemoveItem) {
            $permissionMenuRemoveItem = $authManager->createPermission('menu_remove_item');
            $permissionMenuRemoveItem->description = "Quitar receta o combo de un menú";
            $permissionMenuRemoveItem->ruleName = $paymentRule->name;
            $authManager->add($permissionMenuRemoveItem);
        }

        // Profitability permissions
        $permissionProfitabilityView = $authManager->getPermission('profitability_view');
        if (!$permissionProfitabilityView) {
            $permissionProfitabilityView = $authManager->createPermission('profitability_view');
            $permissionProfitabilityView->description = "Ver rentabilidad";
            $permissionProfitabilityView->ruleName = $paymentRule->name;
            $authManager->add($permissionProfitabilityView);
        }


        // Theoretical profitability permissions
        $permissionTheoreticalProfitabilityView = $authManager->getPermission('theoretical_profitability_view');
        if (!$permissionTheoreticalProfitabilityView) {
            $permissionTheoreticalProfitabilityView = $authManager->createPermission('theoretical_profitability_view');
            $permissionTheoreticalProfitabilityView->description = "Ver rentabilidad teórica";
            $permissionTheoreticalProfitabilityView->ruleName = $paymentRule->name;
            $authManager->add($permissionTheoreticalProfitabilityView);
        }

        // Real profitability permissions
        $permissionRealProfitabilityView = $authManager->getPermission('real_profitability_view');
        if (!$permissionRealProfitabilityView) {
            $permissionRealProfitabilityView = $authManager->createPermission('real_profitability_view');
            $permissionRealProfitabilityView->description = "Ver detalles de rentabilidad real";
            $permissionRealProfitabilityView->ruleName = $paymentRule->name;
            $authManager->add($permissionRealProfitabilityView);
        }

        // Menu Analysis permissions

        $permissionMenuAnalysisView = $authManager->getPermission('menu_analysis_view');
        if (!$permissionMenuAnalysisView) {
            $permissionMenuAnalysisView = $authManager->createPermission('menu_analysis_view');
            $permissionMenuAnalysisView->description = "Ver detalles de análisis de menú";
            $permissionMenuAnalysisView->ruleName = $paymentRule->name;
            $authManager->add($permissionMenuAnalysisView);
        }
        $permissionMenuAnalysisUpdate = $authManager->getPermission('menu_analysis_update');
        if (!$permissionMenuAnalysisUpdate) {
            $permissionMenuAnalysisUpdate = $authManager->createPermission('menu_analysis_update');
            $permissionMenuAnalysisUpdate->description = "Actualizar información de análisis de menú";
            $permissionMenuAnalysisUpdate->ruleName = $paymentRule->name;
            $authManager->add($permissionMenuAnalysisUpdate);
        }

        // Menu Improvements permissions
        $permissionMenuImprovementsView = $authManager->getPermission('menu_improvements_view');
        if (!$permissionMenuImprovementsView) {
            $permissionMenuImprovementsView = $authManager->createPermission('menu_improvements_view');
            $permissionMenuImprovementsView->description = "Ver detalles de mejoras de menú";
            $permissionMenuImprovementsView->ruleName = $paymentRule->name;
            $authManager->add($permissionMenuImprovementsView);
        }
        $permissionMenuImprovementsUpdate = $authManager->getPermission('menu_improvements_update');
        if (!$permissionMenuImprovementsUpdate) {
            $permissionMenuImprovementsUpdate = $authManager->createPermission('menu_improvements_update');
            $permissionMenuImprovementsUpdate->description = "Actualizar información de mejora de menú";
            $permissionMenuImprovementsUpdate->ruleName = $paymentRule->name;
            $authManager->add($permissionMenuImprovementsUpdate);
        }

        // ABC Analysis permissions
        $permissionAbcAnalysisView = $authManager->getPermission('abc_analysis_view');
        if (!$permissionAbcAnalysisView) {
            $permissionAbcAnalysisView = $authManager->createPermission('abc_analysis_view');
            $permissionAbcAnalysisView->description = "Ver análisis ABC";
            $permissionAbcAnalysisView->ruleName = $paymentRule->name;
            $authManager->add($permissionAbcAnalysisView);
        }

        // Movement permissions
        $permissionMovementsList = $authManager->getPermission('movements_list');
        if (!$permissionMovementsList) {
            $permissionMovementsList = $authManager->createPermission('movements_list');
            $permissionMovementsList->description = "Ver lista de movimientos";
            $permissionMovementsList->ruleName = $paymentRule->name;
            $authManager->add($permissionMovementsList);
        }
        $permissionMovementsView = $authManager->getPermission('movements_view');
        if (!$permissionMovementsView) {
            $permissionMovementsView = $authManager->createPermission('movements_view');
            $permissionMovementsView->description = "Ver detalles de movimientos";
            $permissionMovementsView->ruleName = $paymentRule->name;
            $authManager->add($permissionMovementsView);
        }
        $permissionMovementsCreate = $authManager->getPermission('movements_create');
        if (!$permissionMovementsCreate) {
            $permissionMovementsCreate = $authManager->createPermission('movements_create');
            $permissionMovementsCreate->description = "Crear un nuevo movimiento";
            $permissionMovementsCreate->ruleName = $paymentRule->name;
            $authManager->add($permissionMovementsCreate);
        }
        $permissionMovementsUpdate = $authManager->getPermission('movements_update');
        if (!$permissionMovementsUpdate) {
            $permissionMovementsUpdate = $authManager->createPermission('movements_update');
            $permissionMovementsUpdate->description = "Actualizar información de movimiento";
            $permissionMovementsUpdate->ruleName = $paymentRule->name;
            $authManager->add($permissionMovementsUpdate);
        }
        $permissionMovementsDelete = $authManager->getPermission('movements_delete');
        if (!$permissionMovementsDelete) {
            $permissionMovementsDelete = $authManager->createPermission('movements_delete');
            $permissionMovementsDelete->description = "Eliminar un movimiento";
            $permissionMovementsDelete->ruleName = $paymentRule->name;
            $authManager->add($permissionMovementsDelete);
        }
        $permissionMovementsManageBalance = $authManager->getPermission('movements_manage_balance');
        if (!$permissionMovementsManageBalance) {
            $permissionMovementsManageBalance = $authManager->createPermission('movements_manage_balance');
            $permissionMovementsManageBalance->description = "Gestionar saldo inicial";
            $permissionMovementsManageBalance->ruleName = $paymentRule->name;
            $authManager->add($permissionMovementsManageBalance);
        }

        // Requisition permissions (for consumption center users)
        $permissionRequisitionsList = $authManager->getPermission('requisitions_list');
        if (!$permissionRequisitionsList) {
            $permissionRequisitionsList = $authManager->createPermission('requisitions_list');
            $permissionRequisitionsList->description = "Ver lista de requisiciones";
            $permissionRequisitionsList->ruleName = $paymentRule->name;
            $authManager->add($permissionRequisitionsList);
        }
        $permissionRequisitionsView = $authManager->getPermission('requisitions_view');
        if (!$permissionRequisitionsView) {
            $permissionRequisitionsView = $authManager->createPermission('requisitions_view');
            $permissionRequisitionsView->description = "Ver detalles de requisiciones";
            $permissionRequisitionsView->ruleName = $paymentRule->name;
            $authManager->add($permissionRequisitionsView);
        }
        $permissionRequisitionsCreate = $authManager->getPermission('requisitions_create');
        if (!$permissionRequisitionsCreate) {
            $permissionRequisitionsCreate = $authManager->createPermission('requisitions_create');
            $permissionRequisitionsCreate->description = "Crear requisiciones";
            $permissionRequisitionsCreate->ruleName = $paymentRule->name;
            $authManager->add($permissionRequisitionsCreate);
        }
        $permissionRequisitionsConvert = $authManager->getPermission('requisitions_convert');
        if (!$permissionRequisitionsConvert) {
            $permissionRequisitionsConvert = $authManager->createPermission('requisitions_convert');
            $permissionRequisitionsConvert->description = "Convertir requisiciones a salidas";
            $permissionRequisitionsConvert->ruleName = $paymentRule->name;
            $authManager->add($permissionRequisitionsConvert);
        }

        $permissionMatrixBCG = $authManager->getPermission('matrix_bcg');
        if (!$permissionMatrixBCG) {
            $permissionMatrixBCG = $authManager->createPermission('matrix_bcg');
            $permissionMatrixBCG->description = "Ver matríz BCG";
            $permissionMatrixBCG->ruleName = $paymentRule->name;
            $authManager->add($permissionMatrixBCG);
        }

        $permissionSalesView = $authManager->getPermission('sales_view');
        if (!$permissionSalesView) {
            $permissionSalesView = $authManager->createPermission('sales_view');
            $permissionSalesView->description = "Ver ventas";
            $permissionSalesView->ruleName = $paymentRule->name;
            $authManager->add($permissionSalesView);
        }
        $permissionSalesUpdate = $authManager->getPermission('sales_update');
        if (!$permissionSalesUpdate) {
            $permissionSalesUpdate = $authManager->createPermission('sales_update');
            $permissionSalesUpdate->description = "Actualizar ventas";
            $permissionSalesUpdate->ruleName = $paymentRule->name;
            $authManager->add($permissionSalesUpdate);
        }

        $permissionManageUsers = $authManager->getPermission('manage_users');
        if (!$permissionManageUsers) {
            $permissionManageUsers = $authManager->createPermission('manage_users');
            $permissionManageUsers->description = "Gestionar usuarios";
            $permissionManageUsers->ruleName = $paymentRule->name;
            $authManager->add($permissionManageUsers);
        }

        $permissionManageAccount = $authManager->getPermission('manage_account');
        if (!$permissionManageAccount) {
            $permissionManageAccount = $authManager->createPermission('manage_account');
            $permissionManageAccount->description = "Gestionar cuenta";
            $permissionManageAccount->ruleName = $paymentRule->name;
            $authManager->add($permissionManageAccount);
        }


        // add permissions to roles
        // Role Storage
        $authManager->removeChildren($roleStorage);
        $addChildSafely($roleStorage, $permissionMovementsList);
        $addChildSafely($roleStorage, $permissionMovementsCreate);
        $addChildSafely($roleStorage, $permissionMovementsView);
        $addChildSafely($roleStorage, $permissionIngredientsList);
        $addChildSafely($roleStorage, $permissionIngredientsView);
        $addChildSafely($roleStorage, $permissionIngredientsCreate);
        $addChildSafely($roleStorage, $permissionIngredientsUpdate);
        $addChildSafely($roleStorage, $permissionIngredientsDelete);
        $addChildSafely($roleStorage, $permissionProvidersList);
        $addChildSafely($roleStorage, $permissionProvidersView);
        $addChildSafely($roleStorage, $permissionPriceTrendView);

        // Role Storage Admin
        $authManager->removeChildren($roleStorageAdmin);
        $addChildSafely($roleStorageAdmin, $permissionMovementsList);
        $addChildSafely($roleStorageAdmin, $permissionMovementsCreate);
        $addChildSafely($roleStorageAdmin, $permissionMovementsView);
        $addChildSafely($roleStorageAdmin, $permissionRequisitionsList);
        $addChildSafely($roleStorageAdmin, $permissionRequisitionsView);
        $addChildSafely($roleStorageAdmin, $permissionRequisitionsCreate);
        $addChildSafely($roleStorageAdmin, $permissionRequisitionsConvert);
        $addChildSafely($roleStorageAdmin, $permissionIngredientsList);
        $addChildSafely($roleStorageAdmin, $permissionIngredientsView);
        $addChildSafely($roleStorageAdmin, $permissionIngredientsCreate);
        $addChildSafely($roleStorageAdmin, $permissionIngredientsUpdate);
        $addChildSafely($roleStorageAdmin, $permissionIngredientsDelete);
        $addChildSafely($roleStorageAdmin, $permissionProvidersList);
        $addChildSafely($roleStorageAdmin, $permissionProvidersView);
        $addChildSafely($roleStorageAdmin, $permissionProvidersCreate);
        $addChildSafely($roleStorageAdmin, $permissionProvidersUpdate);
        $addChildSafely($roleStorageAdmin, $permissionProvidersDelete);
        $addChildSafely($roleStorageAdmin, $permissionPriceTrendView);

        // Role Chef
        $authManager->removeChildren($roleChef);
        $addChildSafely($roleChef, $permissionIngredientsList);
        $addChildSafely($roleChef, $permissionIngredientsView);
        $addChildSafely($roleChef, $permissionSubrecipeList);
        $addChildSafely($roleChef, $permissionSubrecipeView);
        $addChildSafely($roleChef, $permissionSubrecipeCreate);
        $addChildSafely($roleChef, $permissionSubrecipeUpdate);
        $addChildSafely($roleChef, $permissionSubrecipeDelete);
        $addChildSafely($roleChef, $permissionRecipeList);
        $addChildSafely($roleChef, $permissionRecipeView);
        $addChildSafely($roleChef, $permissionRecipeCreate);
        $addChildSafely($roleChef, $permissionRecipeUpdate);
        $addChildSafely($roleChef, $permissionRecipeDelete);
        $addChildSafely($roleChef, $permissionConvoyList);
        $addChildSafely($roleChef, $permissionConvoyView);
        $addChildSafely($roleChef, $permissionConvoyCreate);
        $addChildSafely($roleChef, $permissionConvoyUpdate);
        $addChildSafely($roleChef, $permissionConvoyDelete);
        $addChildSafely($roleChef, $permissionMenuView);
        $addChildSafely($roleChef, $permissionMenuUpdate);
        $addChildSafely($roleChef, $permissionMenuAddItem);
        $addChildSafely($roleChef, $permissionMenuRemoveItem);

        // Role Executive Chef
        $authManager->removeChildren($roleExecutiveChef);
        $addChildSafely($roleExecutiveChef, $permissionIngredientsList);
        $addChildSafely($roleExecutiveChef, $permissionIngredientsView);
        $addChildSafely($roleExecutiveChef, $permissionIngredientsCreate);
        $addChildSafely($roleExecutiveChef, $permissionIngredientsUpdate);
        $addChildSafely($roleExecutiveChef, $permissionIngredientsDelete);
        $addChildSafely($roleExecutiveChef, $permissionSubrecipeList);
        $addChildSafely($roleExecutiveChef, $permissionSubrecipeView);
        $addChildSafely($roleExecutiveChef, $permissionSubrecipeCreate);
        $addChildSafely($roleExecutiveChef, $permissionSubrecipeUpdate);
        $addChildSafely($roleExecutiveChef, $permissionSubrecipeDelete);
        $addChildSafely($roleExecutiveChef, $permissionRecipeList);
        $addChildSafely($roleExecutiveChef, $permissionRecipeView);
        $addChildSafely($roleExecutiveChef, $permissionRecipeCreate);
        $addChildSafely($roleExecutiveChef, $permissionRecipeUpdate);
        $addChildSafely($roleExecutiveChef, $permissionRecipeDelete);
        $addChildSafely($roleExecutiveChef, $permissionConvoyList);
        $addChildSafely($roleExecutiveChef, $permissionConvoyView);
        $addChildSafely($roleExecutiveChef, $permissionConvoyCreate);
        $addChildSafely($roleExecutiveChef, $permissionConvoyUpdate);
        $addChildSafely($roleExecutiveChef, $permissionConvoyDelete);
        $addChildSafely($roleExecutiveChef, $permissionMenuView);
        $addChildSafely($roleExecutiveChef, $permissionMenuUpdate);
        $addChildSafely($roleExecutiveChef, $permissionMenuAddItem);
        $addChildSafely($roleExecutiveChef, $permissionMenuRemoveItem);
        $addChildSafely($roleExecutiveChef, $permissionProfitabilityView);
        $addChildSafely($roleExecutiveChef, $permissionTheoreticalProfitabilityView);
        $addChildSafely($roleExecutiveChef, $permissionRealProfitabilityView);
        $addChildSafely($roleExecutiveChef, $permissionMenuAnalysisView);
        $addChildSafely($roleExecutiveChef, $permissionAbcAnalysisView);
        $addChildSafely($roleExecutiveChef, $permissionChartsView);
        $addChildSafely($roleExecutiveChef, $permissionMenuImprovementsView);
        $addChildSafely($roleExecutiveChef, $permissionMenuImprovementsUpdate);

        // Role Owner
        $authManager->removeChildren($roleOwner);
        $addChildSafely($roleOwner, $permissionStorageList);
        $addChildSafely($roleOwner, $permissionStorageView);
        $addChildSafely($roleOwner, $permissionSubrecipeList);
        $addChildSafely($roleOwner, $permissionSubrecipeView);
        $addChildSafely($roleOwner, $permissionSubrecipeCreate);
        $addChildSafely($roleOwner, $permissionSubrecipeUpdate);
        $addChildSafely($roleOwner, $permissionSubrecipeDelete);
        $addChildSafely($roleOwner, $permissionRecipeList);
        $addChildSafely($roleOwner, $permissionRecipeView);
        $addChildSafely($roleOwner, $permissionRecipeCreate);
        $addChildSafely($roleOwner, $permissionRecipeUpdate);
        $addChildSafely($roleOwner, $permissionRecipeDelete);
        $addChildSafely($roleOwner, $permissionConvoyList);
        $addChildSafely($roleOwner, $permissionConvoyView);
        $addChildSafely($roleOwner, $permissionConvoyCreate);
        $addChildSafely($roleOwner, $permissionConvoyUpdate);
        $addChildSafely($roleOwner, $permissionConvoyDelete);
        $addChildSafely($roleOwner, $permissionComboList);
        $addChildSafely($roleOwner, $permissionComboView);
        $addChildSafely($roleOwner, $permissionComboCreate);
        $addChildSafely($roleOwner, $permissionComboUpdate);
        $addChildSafely($roleOwner, $permissionComboDelete);
        $addChildSafely($roleOwner, $permissionPriceTrendView);
        $addChildSafely($roleOwner, $permissionChartsView);
        $addChildSafely($roleOwner, $permissionIngredientsList);
        $addChildSafely($roleOwner, $permissionIngredientsView);
        $addChildSafely($roleOwner, $permissionIngredientsCreate);
        $addChildSafely($roleOwner, $permissionIngredientsUpdate);
        $addChildSafely($roleOwner, $permissionIngredientsDelete);
        $addChildSafely($roleOwner, $permissionProvidersList);
        $addChildSafely($roleOwner, $permissionProvidersView);
        $addChildSafely($roleOwner, $permissionProvidersCreate);
        $addChildSafely($roleOwner, $permissionProvidersUpdate);
        $addChildSafely($roleOwner, $permissionProvidersDelete);
        $addChildSafely($roleOwner, $permissionMenuView);
        $addChildSafely($roleOwner, $permissionMenuUpdate);
        $addChildSafely($roleOwner, $permissionMenuAddItem);
        $addChildSafely($roleOwner, $permissionMenuRemoveItem);
        $addChildSafely($roleOwner, $permissionProfitabilityView);
        $addChildSafely($roleOwner, $permissionTheoreticalProfitabilityView);
        $addChildSafely($roleOwner, $permissionRealProfitabilityView);
        $addChildSafely($roleOwner, $permissionMenuAnalysisView);
        $addChildSafely($roleOwner, $permissionMenuAnalysisUpdate);
        $addChildSafely($roleOwner, $permissionMenuImprovementsView);
        $addChildSafely($roleOwner, $permissionMenuImprovementsUpdate);
        $addChildSafely($roleOwner, $permissionAbcAnalysisView);
        $addChildSafely($roleOwner, $permissionMovementsList);
        $addChildSafely($roleOwner, $permissionMovementsView);
        $addChildSafely($roleOwner, $permissionMovementsCreate);
        $addChildSafely($roleOwner, $permissionMovementsUpdate);
        $addChildSafely($roleOwner, $permissionMovementsDelete);

        // Role Administrator
        $authManager->removeChildren($roleAdministrator);
        $addChildSafely($roleAdministrator, $permissionStorageList);
        $addChildSafely($roleAdministrator, $permissionStorageView);
        $addChildSafely($roleAdministrator, $permissionSubrecipeList);
        $addChildSafely($roleAdministrator, $permissionSubrecipeView);
        $addChildSafely($roleAdministrator, $permissionSubrecipeCreate);
        $addChildSafely($roleAdministrator, $permissionSubrecipeUpdate);
        $addChildSafely($roleAdministrator, $permissionSubrecipeDelete);
        $addChildSafely($roleAdministrator, $permissionRecipeList);
        $addChildSafely($roleAdministrator, $permissionRecipeView);
        $addChildSafely($roleAdministrator, $permissionRecipeCreate);
        $addChildSafely($roleAdministrator, $permissionRecipeUpdate);
        $addChildSafely($roleAdministrator, $permissionRecipeDelete);
        $addChildSafely($roleAdministrator, $permissionConvoyList);
        $addChildSafely($roleAdministrator, $permissionConvoyView);
        $addChildSafely($roleAdministrator, $permissionConvoyCreate);
        $addChildSafely($roleAdministrator, $permissionConvoyUpdate);
        $addChildSafely($roleAdministrator, $permissionConvoyDelete);
        $addChildSafely($roleAdministrator, $permissionComboList);
        $addChildSafely($roleAdministrator, $permissionComboView);
        $addChildSafely($roleAdministrator, $permissionComboCreate);
        $addChildSafely($roleAdministrator, $permissionComboUpdate);
        $addChildSafely($roleAdministrator, $permissionComboDelete);
        $addChildSafely($roleAdministrator, $permissionPriceTrendView);
        $addChildSafely($roleAdministrator, $permissionChartsView);
        $addChildSafely($roleAdministrator, $permissionIngredientsList);
        $addChildSafely($roleAdministrator, $permissionIngredientsView);
        $addChildSafely($roleAdministrator, $permissionIngredientsCreate);
        $addChildSafely($roleAdministrator, $permissionIngredientsUpdate);
        $addChildSafely($roleAdministrator, $permissionIngredientsDelete);
        $addChildSafely($roleAdministrator, $permissionProvidersList);
        $addChildSafely($roleAdministrator, $permissionProvidersView);
        $addChildSafely($roleAdministrator, $permissionProvidersCreate);
        $addChildSafely($roleAdministrator, $permissionProvidersUpdate);
        $addChildSafely($roleAdministrator, $permissionProvidersDelete);
        $addChildSafely($roleAdministrator, $permissionMenuView);
        $addChildSafely($roleAdministrator, $permissionMenuUpdate);
        $addChildSafely($roleAdministrator, $permissionMenuAddItem);
        $addChildSafely($roleAdministrator, $permissionMenuRemoveItem);
        $addChildSafely($roleAdministrator, $permissionProfitabilityView);
        $addChildSafely($roleAdministrator, $permissionTheoreticalProfitabilityView);
        $addChildSafely($roleAdministrator, $permissionRealProfitabilityView);
        $addChildSafely($roleAdministrator, $permissionMenuAnalysisView);
        $addChildSafely($roleAdministrator, $permissionMenuAnalysisUpdate);
        $addChildSafely($roleAdministrator, $permissionMenuImprovementsView);
        $addChildSafely($roleAdministrator, $permissionMenuImprovementsUpdate);
        $addChildSafely($roleAdministrator, $permissionAbcAnalysisView);
        $addChildSafely($roleAdministrator, $permissionMovementsList);
        $addChildSafely($roleAdministrator, $permissionMovementsView);
        $addChildSafely($roleAdministrator, $permissionMovementsCreate);
        $addChildSafely($roleAdministrator, $permissionMovementsUpdate);
        $addChildSafely($roleAdministrator, $permissionMovementsDelete);

        // Create additional permissions
        $permissionDashboardView = $authManager->getPermission('dashboard_view');
        if (!$permissionDashboardView) {
            $permissionDashboardView = $authManager->createPermission('dashboard_view');
            $permissionDashboardView->description = "Ver dashboard";
            $permissionDashboardView->ruleName = $paymentRule->name;
            $authManager->add($permissionDashboardView);
        }

        // Users permissions
        $permissionUsersList = $authManager->getPermission('users_list');
        if (!$permissionUsersList) {
            $permissionUsersList = $authManager->createPermission('users_list');
            $permissionUsersList->description = "Ver lista de usuarios";
            $permissionUsersList->ruleName = $paymentRule->name;
            $authManager->add($permissionUsersList);
        }
        $permissionUsersView = $authManager->getPermission('users_view');
        if (!$permissionUsersView) {
            $permissionUsersView = $authManager->createPermission('users_view');
            $permissionUsersView->description = "Ver detalles de usuario";
            $permissionUsersView->ruleName = $paymentRule->name;
            $authManager->add($permissionUsersView);
        }
        $permissionUsersCreate = $authManager->getPermission('users_create');
        if (!$permissionUsersCreate) {
            $permissionUsersCreate = $authManager->createPermission('users_create');
            $permissionUsersCreate->description = "Crear un nuevo usuario";
            $permissionUsersCreate->ruleName = $paymentRule->name;
            $authManager->add($permissionUsersCreate);
        }
        $permissionUsersUpdate = $authManager->getPermission('users_update');
        if (!$permissionUsersUpdate) {
            $permissionUsersUpdate = $authManager->createPermission('users_update');
            $permissionUsersUpdate->description = "Actualizar información de usuario";
            $permissionUsersUpdate->ruleName = $paymentRule->name;
            $authManager->add($permissionUsersUpdate);
        }
        $permissionUsersDelete = $authManager->getPermission('users_delete');
        if (!$permissionUsersDelete) {
            $permissionUsersDelete = $authManager->createPermission('users_delete');
            $permissionUsersDelete->description = "Eliminar un usuario";
            $permissionUsersDelete->ruleName = $paymentRule->name;
            $authManager->add($permissionUsersDelete);
        }

        // Roles permissions
        $permissionRolesList = $authManager->getPermission('roles_list');
        if (!$permissionRolesList) {
            $permissionRolesList = $authManager->createPermission('roles_list');
            $permissionRolesList->description = "Ver lista de roles";
            $permissionRolesList->ruleName = $paymentRule->name;
            $authManager->add($permissionRolesList);
        }
        $permissionRolesView = $authManager->getPermission('roles_view');
        if (!$permissionRolesView) {
            $permissionRolesView = $authManager->createPermission('roles_view');
            $permissionRolesView->description = "Ver detalles de rol";
            $permissionRolesView->ruleName = $paymentRule->name;
            $authManager->add($permissionRolesView);
        }
        $permissionRolesCreate = $authManager->getPermission('roles_create');
        if (!$permissionRolesCreate) {
            $permissionRolesCreate = $authManager->createPermission('roles_create');
            $permissionRolesCreate->description = "Crear un nuevo rol";
            $permissionRolesCreate->ruleName = $paymentRule->name;
            $authManager->add($permissionRolesCreate);
        }
        $permissionRolesUpdate = $authManager->getPermission('roles_update');
        if (!$permissionRolesUpdate) {
            $permissionRolesUpdate = $authManager->createPermission('roles_update');
            $permissionRolesUpdate->description = "Actualizar información de rol";
            $permissionRolesUpdate->ruleName = $paymentRule->name;
            $authManager->add($permissionRolesUpdate);
        }
        $permissionRolesDelete = $authManager->getPermission('roles_delete');
        if (!$permissionRolesDelete) {
            $permissionRolesDelete = $authManager->createPermission('roles_delete');
            $permissionRolesDelete->description = "Eliminar un rol";
            $permissionRolesDelete->ruleName = $paymentRule->name;
            $authManager->add($permissionRolesDelete);
        }

        // Inventory permissions (assuming this is different from ingredients)
        $permissionInventoryList = $authManager->getPermission('inventory_list');
        if (!$permissionInventoryList) {
            $permissionInventoryList = $authManager->createPermission('inventory_list');
            $permissionInventoryList->description = "Ver lista de inventario";
            $permissionInventoryList->ruleName = $paymentRule->name;
            $authManager->add($permissionInventoryList);
        }
        $permissionInventoryView = $authManager->getPermission('inventory_view');
        if (!$permissionInventoryView) {
            $permissionInventoryView = $authManager->createPermission('inventory_view');
            $permissionInventoryView->description = "Ver detalles de inventario";
            $permissionInventoryView->ruleName = $paymentRule->name;
            $authManager->add($permissionInventoryView);
        }
        $permissionInventoryCreate = $authManager->getPermission('inventory_create');
        if (!$permissionInventoryCreate) {
            $permissionInventoryCreate = $authManager->createPermission('inventory_create');
            $permissionInventoryCreate->description = "Crear un nuevo elemento de inventario";
            $permissionInventoryCreate->ruleName = $paymentRule->name;
            $authManager->add($permissionInventoryCreate);
        }
        $permissionInventoryUpdate = $authManager->getPermission('inventory_update');
        if (!$permissionInventoryUpdate) {
            $permissionInventoryUpdate = $authManager->createPermission('inventory_update');
            $permissionInventoryUpdate->description = "Actualizar información de inventario";
            $permissionInventoryUpdate->ruleName = $paymentRule->name;
            $authManager->add($permissionInventoryUpdate);
        }
        $permissionInventoryDelete = $authManager->getPermission('inventory_delete');
        if (!$permissionInventoryDelete) {
            $permissionInventoryDelete = $authManager->createPermission('inventory_delete');
            $permissionInventoryDelete->description = "Eliminar un elemento de inventario";
            $permissionInventoryDelete->ruleName = $paymentRule->name;
            $authManager->add($permissionInventoryDelete);
        }

        // Create new roles
        $roleGeneralManager = $authManager->getRole('general_manager');
        if (!$roleGeneralManager) {
            $roleGeneralManager = $authManager->createRole('general_manager');
            $roleGeneralManager->description = 'Gerente General';
            $authManager->add($roleGeneralManager);
        }

        $roleSousChef = $authManager->getRole('sous_chef');
        if (!$roleSousChef) {
            $roleSousChef = $authManager->createRole('sous_chef');
            $roleSousChef->description = 'Sous Chef';
            $authManager->add($roleSousChef);
        }

        $rolePurchasingManager = $authManager->getRole('purchasing_manager');
        if (!$rolePurchasingManager) {
            $rolePurchasingManager = $authManager->createRole('purchasing_manager');
            $rolePurchasingManager->description = 'Encargado de compras';
            $authManager->add($rolePurchasingManager);
        }

        $roleMaintenanceChief = $authManager->getRole('maintenance_chief');
        if (!$roleMaintenanceChief) {
            $roleMaintenanceChief = $authManager->createRole('maintenance_chief');
            $roleMaintenanceChief->description = 'Jefe de Mantenimiento';
            $authManager->add($roleMaintenanceChief);
        }

        $roleWaiterCaptain = $authManager->getRole('waiter_captain');
        if (!$roleWaiterCaptain) {
            $roleWaiterCaptain = $authManager->createRole('waiter_captain');
            $roleWaiterCaptain->description = 'Capitán de Meseros';
            $authManager->add($roleWaiterCaptain);
        }

        $roleAccountant = $authManager->getRole('accountant');
        if (!$roleAccountant) {
            $roleAccountant = $authManager->createRole('accountant');
            $roleAccountant->description = 'Contador';
            $authManager->add($roleAccountant);
        }

        $roleWarehouseAssistant = $authManager->getRole('warehouse_assistant');
        if (!$roleWarehouseAssistant) {
            $roleWarehouseAssistant = $authManager->createRole('warehouse_assistant');
            $roleWarehouseAssistant->description = 'Asistente de Almacén';
            $authManager->add($roleWarehouseAssistant);
        }

        $roleExternalConsultant = $authManager->getRole('external_consultant');
        if (!$roleExternalConsultant) {
            $roleExternalConsultant = $authManager->createRole('external_consultant');
            $roleExternalConsultant->description = 'Consultor Externo';
            $authManager->add($roleExternalConsultant);
        }

        $roleAdministrativeAssistant = $authManager->getRole('administrative_assistant');
        if (!$roleAdministrativeAssistant) {
            $roleAdministrativeAssistant = $authManager->createRole('administrative_assistant');
            $roleAdministrativeAssistant->description = 'Asistente Administrativo';
            $authManager->add($roleAdministrativeAssistant);
        }

        $roleMenuManager = $authManager->getRole('menu_manager');
        if (!$roleMenuManager) {
            $roleMenuManager = $authManager->createRole('menu_manager');
            $roleMenuManager->description = 'Gerente de Menú';
            $authManager->add($roleMenuManager);
        }

        // Role Consumption Requester (Solicitante de Consumo)
        $roleConsumptionRequester = $authManager->getRole('consumption_requester');
        if (!$roleConsumptionRequester) {
            $roleConsumptionRequester = $authManager->createRole('consumption_requester');
            $roleConsumptionRequester->description = 'Solicitante de Consumo - Solo puede crear y ver requisiciones';
            $authManager->add($roleConsumptionRequester);
        }



        // Assign permissions to General Manager
        $addChildSafely($roleGeneralManager, $permissionDashboardView);
        $addChildSafely($roleGeneralManager, $permissionUsersList);
        $addChildSafely($roleGeneralManager, $permissionUsersView);
        $addChildSafely($roleGeneralManager, $permissionUsersCreate);
        $addChildSafely($roleGeneralManager, $permissionUsersUpdate);
        $addChildSafely($roleGeneralManager, $permissionUsersDelete);
        $addChildSafely($roleGeneralManager, $permissionRolesList);
        $addChildSafely($roleGeneralManager, $permissionRolesView);
        $addChildSafely($roleGeneralManager, $permissionRolesCreate);
        $addChildSafely($roleGeneralManager, $permissionRolesUpdate);
        $addChildSafely($roleGeneralManager, $permissionRolesDelete);
        $addChildSafely($roleGeneralManager, $permissionRecipeList);
        $addChildSafely($roleGeneralManager, $permissionRecipeView);
        $addChildSafely($roleGeneralManager, $permissionRecipeCreate);
        $addChildSafely($roleGeneralManager, $permissionRecipeUpdate);
        $addChildSafely($roleGeneralManager, $permissionRecipeDelete);
        $addChildSafely($roleGeneralManager, $permissionIngredientsList);
        $addChildSafely($roleGeneralManager, $permissionIngredientsView);
        $addChildSafely($roleGeneralManager, $permissionIngredientsCreate);
        $addChildSafely($roleGeneralManager, $permissionIngredientsUpdate);
        $addChildSafely($roleGeneralManager, $permissionIngredientsDelete);
        $addChildSafely($roleGeneralManager, $permissionProvidersList);
        $addChildSafely($roleGeneralManager, $permissionProvidersView);
        $addChildSafely($roleGeneralManager, $permissionProvidersCreate);
        $addChildSafely($roleGeneralManager, $permissionProvidersUpdate);
        $addChildSafely($roleGeneralManager, $permissionProvidersDelete);
        $addChildSafely($roleGeneralManager, $permissionInventoryList);
        $addChildSafely($roleGeneralManager, $permissionInventoryView);
        $addChildSafely($roleGeneralManager, $permissionInventoryCreate);
        $addChildSafely($roleGeneralManager, $permissionInventoryUpdate);
        $addChildSafely($roleGeneralManager, $permissionInventoryDelete);
        $addChildSafely($roleGeneralManager, $permissionMovementsList);
        $addChildSafely($roleGeneralManager, $permissionMovementsView);
        $addChildSafely($roleGeneralManager, $permissionMovementsCreate);
        $addChildSafely($roleGeneralManager, $permissionMovementsUpdate);
        $addChildSafely($roleGeneralManager, $permissionMovementsDelete);

        // Assign permissions to Sous Chef
        $addChildSafely($roleSousChef, $permissionDashboardView);
        $addChildSafely($roleSousChef, $permissionRecipeList);
        $addChildSafely($roleSousChef, $permissionRecipeView);
        $addChildSafely($roleSousChef, $permissionRecipeCreate);
        $addChildSafely($roleSousChef, $permissionRecipeUpdate);
        $addChildSafely($roleSousChef, $permissionRecipeDelete);
        $addChildSafely($roleSousChef, $permissionIngredientsList);
        $addChildSafely($roleSousChef, $permissionIngredientsView);
        $addChildSafely($roleSousChef, $permissionIngredientsCreate);
        $addChildSafely($roleSousChef, $permissionIngredientsUpdate);
        $addChildSafely($roleSousChef, $permissionIngredientsDelete);
        $addChildSafely($roleSousChef, $permissionInventoryList);
        $addChildSafely($roleSousChef, $permissionInventoryView);
        $addChildSafely($roleSousChef, $permissionInventoryCreate);
        $addChildSafely($roleSousChef, $permissionInventoryUpdate);
        $addChildSafely($roleSousChef, $permissionInventoryDelete);
        $addChildSafely($roleSousChef, $permissionMovementsList);
        $addChildSafely($roleSousChef, $permissionMovementsView);
        $addChildSafely($roleSousChef, $permissionMovementsCreate);
        $addChildSafely($roleSousChef, $permissionMovementsUpdate);
        $addChildSafely($roleSousChef, $permissionMovementsDelete);

        // Assign permissions to Purchasing Manager
        $addChildSafely($rolePurchasingManager, $permissionDashboardView);
        $addChildSafely($rolePurchasingManager, $permissionProvidersList);
        $addChildSafely($rolePurchasingManager, $permissionProvidersView);
        $addChildSafely($rolePurchasingManager, $permissionProvidersCreate);
        $addChildSafely($rolePurchasingManager, $permissionProvidersUpdate);
        $addChildSafely($rolePurchasingManager, $permissionProvidersDelete);
        $addChildSafely($rolePurchasingManager, $permissionIngredientsList);
        $addChildSafely($rolePurchasingManager, $permissionIngredientsView);
        $addChildSafely($rolePurchasingManager, $permissionIngredientsCreate);
        $addChildSafely($rolePurchasingManager, $permissionIngredientsUpdate);
        $addChildSafely($rolePurchasingManager, $permissionIngredientsDelete);
        $addChildSafely($rolePurchasingManager, $permissionInventoryList);
        $addChildSafely($rolePurchasingManager, $permissionInventoryView);
        $addChildSafely($rolePurchasingManager, $permissionInventoryCreate);
        $addChildSafely($rolePurchasingManager, $permissionInventoryUpdate);
        $addChildSafely($rolePurchasingManager, $permissionInventoryDelete);
        $addChildSafely($rolePurchasingManager, $permissionMovementsList);
        $addChildSafely($rolePurchasingManager, $permissionMovementsView);
        $addChildSafely($rolePurchasingManager, $permissionMovementsCreate);
        $addChildSafely($rolePurchasingManager, $permissionMovementsUpdate);
        $addChildSafely($rolePurchasingManager, $permissionMovementsDelete);

        // Assign permissions to Maintenance Chief
        $addChildSafely($roleMaintenanceChief, $permissionDashboardView);
        $addChildSafely($roleMaintenanceChief, $permissionInventoryList);
        $addChildSafely($roleMaintenanceChief, $permissionInventoryView);
        $addChildSafely($roleMaintenanceChief, $permissionInventoryCreate);
        $addChildSafely($roleMaintenanceChief, $permissionInventoryUpdate);
        $addChildSafely($roleMaintenanceChief, $permissionInventoryDelete);
        $addChildSafely($roleMaintenanceChief, $permissionMovementsList);
        $addChildSafely($roleMaintenanceChief, $permissionMovementsView);
        $addChildSafely($roleMaintenanceChief, $permissionMovementsCreate);
        $addChildSafely($roleMaintenanceChief, $permissionMovementsUpdate);
        $addChildSafely($roleMaintenanceChief, $permissionMovementsDelete);

        // Assign permissions to Waiter Captain
        $addChildSafely($roleWaiterCaptain, $permissionDashboardView);
        $addChildSafely($roleWaiterCaptain, $permissionRecipeList);
        $addChildSafely($roleWaiterCaptain, $permissionRecipeView);
        $addChildSafely($roleWaiterCaptain, $permissionInventoryList);
        $addChildSafely($roleWaiterCaptain, $permissionInventoryView);
        $addChildSafely($roleWaiterCaptain, $permissionMovementsList);
        $addChildSafely($roleWaiterCaptain, $permissionMovementsView);

        // Assign permissions to Accountant
        $addChildSafely($roleAccountant, $permissionDashboardView);
        $addChildSafely($roleAccountant, $permissionProvidersList);
        $addChildSafely($roleAccountant, $permissionProvidersView);
        $addChildSafely($roleAccountant, $permissionInventoryList);
        $addChildSafely($roleAccountant, $permissionInventoryView);
        $addChildSafely($roleAccountant, $permissionMovementsList);
        $addChildSafely($roleAccountant, $permissionMovementsView);
        $addChildSafely($roleAccountant, $permissionMovementsCreate);
        $addChildSafely($roleAccountant, $permissionMovementsUpdate);
        $addChildSafely($roleAccountant, $permissionMovementsDelete);

        // Assign permissions to Warehouse Assistant
        $addChildSafely($roleWarehouseAssistant, $permissionDashboardView);
        $addChildSafely($roleWarehouseAssistant, $permissionInventoryList);
        $addChildSafely($roleWarehouseAssistant, $permissionInventoryView);
        $addChildSafely($roleWarehouseAssistant, $permissionInventoryCreate);
        $addChildSafely($roleWarehouseAssistant, $permissionInventoryUpdate);
        $addChildSafely($roleWarehouseAssistant, $permissionInventoryDelete);
        $addChildSafely($roleWarehouseAssistant, $permissionMovementsList);
        $addChildSafely($roleWarehouseAssistant, $permissionMovementsView);
        $addChildSafely($roleWarehouseAssistant, $permissionMovementsCreate);
        $addChildSafely($roleWarehouseAssistant, $permissionMovementsUpdate);
        $addChildSafely($roleWarehouseAssistant, $permissionMovementsDelete);

        // Assign permissions to External Consultant
        $addChildSafely($roleExternalConsultant, $permissionDashboardView);
        $addChildSafely($roleExternalConsultant, $permissionRecipeList);
        $addChildSafely($roleExternalConsultant, $permissionRecipeView);
        $addChildSafely($roleExternalConsultant, $permissionIngredientsList);
        $addChildSafely($roleExternalConsultant, $permissionIngredientsView);
        $addChildSafely($roleExternalConsultant, $permissionProvidersList);
        $addChildSafely($roleExternalConsultant, $permissionProvidersView);
        $addChildSafely($roleExternalConsultant, $permissionInventoryList);
        $addChildSafely($roleExternalConsultant, $permissionInventoryView);
        $addChildSafely($roleExternalConsultant, $permissionMovementsList);
        $addChildSafely($roleExternalConsultant, $permissionMovementsView);

        // Assign permissions to Administrative Assistant
        $addChildSafely($roleAdministrativeAssistant, $permissionDashboardView);
        $addChildSafely($roleAdministrativeAssistant, $permissionUsersList);
        $addChildSafely($roleAdministrativeAssistant, $permissionUsersView);
        $addChildSafely($roleAdministrativeAssistant, $permissionUsersCreate);
        $addChildSafely($roleAdministrativeAssistant, $permissionUsersUpdate);
        $addChildSafely($roleAdministrativeAssistant, $permissionUsersDelete);
        $addChildSafely($roleAdministrativeAssistant, $permissionRecipeList);
        $addChildSafely($roleAdministrativeAssistant, $permissionRecipeView);
        $addChildSafely($roleAdministrativeAssistant, $permissionIngredientsList);
        $addChildSafely($roleAdministrativeAssistant, $permissionIngredientsView);
        $addChildSafely($roleAdministrativeAssistant, $permissionProvidersList);
        $addChildSafely($roleAdministrativeAssistant, $permissionProvidersView);
        $addChildSafely($roleAdministrativeAssistant, $permissionInventoryList);
        $addChildSafely($roleAdministrativeAssistant, $permissionInventoryView);
        $addChildSafely($roleAdministrativeAssistant, $permissionMovementsList);
        $addChildSafely($roleAdministrativeAssistant, $permissionMovementsView);

        // Assign permissions to Menu Manager
        $addChildSafely($roleMenuManager, $permissionDashboardView);
        $addChildSafely($roleMenuManager, $permissionRecipeList);
        $addChildSafely($roleMenuManager, $permissionRecipeView);
        $addChildSafely($roleMenuManager, $permissionRecipeCreate);
        $addChildSafely($roleMenuManager, $permissionRecipeUpdate);
        $addChildSafely($roleMenuManager, $permissionRecipeDelete);
        $addChildSafely($roleMenuManager, $permissionIngredientsList);
        $addChildSafely($roleMenuManager, $permissionIngredientsView);
        $addChildSafely($roleMenuManager, $permissionIngredientsCreate);
        $addChildSafely($roleMenuManager, $permissionIngredientsUpdate);
        $addChildSafely($roleMenuManager, $permissionIngredientsDelete);

        // Assign permissions to Consumption Requester (Solicitante de Consumo)
        // Este rol solo puede crear y ver requisiciones, NO puede convertirlas a salidas
        $addChildSafely($roleConsumptionRequester, $permissionDashboardView);
        $addChildSafely($roleConsumptionRequester, $permissionRequisitionsList);
        $addChildSafely($roleConsumptionRequester, $permissionRequisitionsView);
        $addChildSafely($roleConsumptionRequester, $permissionRequisitionsCreate);
        $addChildSafely($roleConsumptionRequester, $permissionIngredientsList);
        $addChildSafely($roleConsumptionRequester, $permissionIngredientsView);
        $addChildSafely($roleConsumptionRequester, $permissionMovementsList);
        $addChildSafely($roleConsumptionRequester, $permissionMovementsView);

    }

    /**
     * Crea un nuevo usuario super admin
     * Uso: php yii rbac/create-admin <username> <email> <password>
     * Ejemplo: php yii rbac/create-admin example example@gmail.com "MiPassword123!"
     */
    public function actionCreateAdmin($username, $email, $password)
    {
        $authManager = \Yii::$app->authManager;
        
        echo "Creando usuario super admin...\n";
        echo "Usuario: {$username}\n";
        echo "Email: {$email}\n\n";
        
        // Verificar si el usuario ya existe
        $existingUser = \common\models\User::findOne(['username' => $username]);
        if ($existingUser) {
            echo "ERROR: El usuario '{$username}' ya existe!\n";
            return 1;
        }
        
        $existingEmail = \common\models\User::findOne(['email' => $email]);
        if ($existingEmail) {
            echo "ERROR: El email '{$email}' ya está registrado!\n";
            return 1;
        }
        
        $transaction = \Yii::$app->db->beginTransaction();
        
        try {
            // Crear el usuario
            $user = new \common\models\User();
            $user->username = $username;
            $user->email = $email;
            $user->password = $password; // El modelo se encarga de hashearlo
            $user->created_at = time();
            $user->auth_key = \Yii::$app->security->generateRandomString();
            $user->confirmed_at = time(); // Usuario confirmado automáticamente
            
            if (!$user->save()) {
                echo "ERROR al crear el usuario:\n";
                foreach ($user->getErrors() as $field => $errors) {
                    foreach ($errors as $error) {
                        echo "  - {$field}: {$error}\n";
                    }
                }
                throw new \Exception("No se pudo crear el usuario");
            }
            
            echo "✓ Usuario creado exitosamente (ID: {$user->id})\n";
            
            // Crear el perfil
            $profile = new \common\models\Profile();
            $profile->user_id = $user->id;
            $profile->name = ucfirst(explode('.', $username)[0]); // Capitalizar primer nombre
            
            if ($profile->save()) {
                echo "✓ Perfil creado\n";
            } else {
                echo "ADVERTENCIA: No se pudo crear el perfil (el usuario fue creado)\n";
            }
            
            // Asignar rol de admin
            $adminRole = $authManager->getRole('admin');
            if (!$adminRole) {
                echo "ERROR: El rol 'admin' no existe. Ejecuta primero: php yii rbac/init\n";
                throw new \Exception("Rol admin no encontrado");
            }
            
            $authManager->assign($adminRole, $user->id);
            echo "✓ Rol 'admin' asignado al usuario\n\n";
            
            $transaction->commit();
            
            echo "========================================\n";
            echo "USUARIO SUPER ADMIN CREADO EXITOSAMENTE\n";
            echo "========================================\n";
            echo "Usuario: {$username}\n";
            echo "Email:   {$email}\n";
            echo "Rol:     admin (super administrador)\n";
            echo "Estado:  Confirmado y activo\n";
            echo "========================================\n";
            
            return 0;
            
        } catch (\Exception $e) {
            $transaction->rollBack();
            echo "\nERROR: No se pudo crear el usuario: {$e->getMessage()}\n";
            return 1;
        }
    }

}
