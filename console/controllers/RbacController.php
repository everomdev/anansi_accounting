<?php

namespace console\controllers;

use backend\rules\PaymentRule;
use yii\console\Controller;

class RbacController extends Controller
{
    public function actionInit()
    {
        $authManager = \Yii::$app->authManager;

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
        $authManager->addChild($roleStorage, $permissionMovementsList);
        $authManager->addChild($roleStorage, $permissionMovementsCreate);
        $authManager->addChild($roleStorage, $permissionMovementsView);
        $authManager->addChild($roleStorage, $permissionIngredientsList);
        $authManager->addChild($roleStorage, $permissionIngredientsView);
        $authManager->addChild($roleStorage, $permissionIngredientsCreate);
        $authManager->addChild($roleStorage, $permissionIngredientsUpdate);
        $authManager->addChild($roleStorage, $permissionIngredientsDelete);
        $authManager->addChild($roleStorage, $permissionProvidersList);
        $authManager->addChild($roleStorage, $permissionProvidersView);
        $authManager->addChild($roleStorage, $permissionPriceTrendView);

        // Role Storage Admin
        $authManager->removeChildren($roleStorageAdmin);
        $authManager->addChild($roleStorageAdmin, $permissionMovementsList);
        $authManager->addChild($roleStorageAdmin, $permissionMovementsCreate);
        $authManager->addChild($roleStorageAdmin, $permissionMovementsView);
        $authManager->addChild($roleStorageAdmin, $permissionIngredientsList);
        $authManager->addChild($roleStorageAdmin, $permissionIngredientsView);
        $authManager->addChild($roleStorageAdmin, $permissionIngredientsCreate);
        $authManager->addChild($roleStorageAdmin, $permissionIngredientsUpdate);
        $authManager->addChild($roleStorageAdmin, $permissionIngredientsDelete);
        $authManager->addChild($roleStorageAdmin, $permissionProvidersList);
        $authManager->addChild($roleStorageAdmin, $permissionProvidersView);
        $authManager->addChild($roleStorageAdmin, $permissionProvidersCreate);
        $authManager->addChild($roleStorageAdmin, $permissionProvidersUpdate);
        $authManager->addChild($roleStorageAdmin, $permissionProvidersDelete);
        $authManager->addChild($roleStorageAdmin, $permissionPriceTrendView);

        // Role Chef
        $authManager->removeChildren($roleChef);
        $authManager->addChild($roleChef, $permissionIngredientsList);
        $authManager->addChild($roleChef, $permissionIngredientsView);
        $authManager->addChild($roleChef, $permissionSubrecipeList);
        $authManager->addChild($roleChef, $permissionSubrecipeView);
        $authManager->addChild($roleChef, $permissionSubrecipeCreate);
        $authManager->addChild($roleChef, $permissionSubrecipeUpdate);
        $authManager->addChild($roleChef, $permissionSubrecipeDelete);
        $authManager->addChild($roleChef, $permissionRecipeList);
        $authManager->addChild($roleChef, $permissionRecipeView);
        $authManager->addChild($roleChef, $permissionRecipeCreate);
        $authManager->addChild($roleChef, $permissionRecipeUpdate);
        $authManager->addChild($roleChef, $permissionRecipeDelete);
        $authManager->addChild($roleChef, $permissionConvoyList);
        $authManager->addChild($roleChef, $permissionConvoyView);
        $authManager->addChild($roleChef, $permissionConvoyCreate);
        $authManager->addChild($roleChef, $permissionConvoyUpdate);
        $authManager->addChild($roleChef, $permissionConvoyDelete);
        $authManager->addChild($roleChef, $permissionMenuView);
        $authManager->addChild($roleChef, $permissionMenuUpdate);
        $authManager->addChild($roleChef, $permissionMenuAddItem);
        $authManager->addChild($roleChef, $permissionMenuRemoveItem);

        // Role Executive Chef
        $authManager->removeChildren($roleExecutiveChef);
        $authManager->addChild($roleExecutiveChef, $permissionIngredientsList);
        $authManager->addChild($roleExecutiveChef, $permissionIngredientsView);
        $authManager->addChild($roleExecutiveChef, $permissionIngredientsCreate);
        $authManager->addChild($roleExecutiveChef, $permissionIngredientsUpdate);
        $authManager->addChild($roleExecutiveChef, $permissionIngredientsDelete);
        $authManager->addChild($roleExecutiveChef, $permissionSubrecipeList);
        $authManager->addChild($roleExecutiveChef, $permissionSubrecipeView);
        $authManager->addChild($roleExecutiveChef, $permissionSubrecipeCreate);
        $authManager->addChild($roleExecutiveChef, $permissionSubrecipeUpdate);
        $authManager->addChild($roleExecutiveChef, $permissionSubrecipeDelete);
        $authManager->addChild($roleExecutiveChef, $permissionRecipeList);
        $authManager->addChild($roleExecutiveChef, $permissionRecipeView);
        $authManager->addChild($roleExecutiveChef, $permissionRecipeCreate);
        $authManager->addChild($roleExecutiveChef, $permissionRecipeUpdate);
        $authManager->addChild($roleExecutiveChef, $permissionRecipeDelete);
        $authManager->addChild($roleExecutiveChef, $permissionConvoyList);
        $authManager->addChild($roleExecutiveChef, $permissionConvoyView);
        $authManager->addChild($roleExecutiveChef, $permissionConvoyCreate);
        $authManager->addChild($roleExecutiveChef, $permissionConvoyUpdate);
        $authManager->addChild($roleExecutiveChef, $permissionConvoyDelete);
        $authManager->addChild($roleExecutiveChef, $permissionMenuView);
        $authManager->addChild($roleExecutiveChef, $permissionMenuUpdate);
        $authManager->addChild($roleExecutiveChef, $permissionMenuAddItem);
        $authManager->addChild($roleExecutiveChef, $permissionMenuRemoveItem);
        $authManager->addChild($roleExecutiveChef, $permissionProfitabilityView);
        $authManager->addChild($roleExecutiveChef, $permissionTheoreticalProfitabilityView);
        $authManager->addChild($roleExecutiveChef, $permissionRealProfitabilityView);
        $authManager->addChild($roleExecutiveChef, $permissionMenuAnalysisView);
        $authManager->addChild($roleExecutiveChef, $permissionAbcAnalysisView);
        $authManager->addChild($roleExecutiveChef, $permissionChartsView);
        $authManager->addChild($roleExecutiveChef, $permissionMenuImprovementsView);
        $authManager->addChild($roleExecutiveChef, $permissionMenuImprovementsUpdate);

        // Role Owner
        $authManager->removeChildren($roleOwner);
        $authManager->addChild($roleOwner, $permissionStorageList);
        $authManager->addChild($roleOwner, $permissionStorageView);
        $authManager->addChild($roleOwner, $permissionSubrecipeList);
        $authManager->addChild($roleOwner, $permissionSubrecipeView);
        $authManager->addChild($roleOwner, $permissionSubrecipeCreate);
        $authManager->addChild($roleOwner, $permissionSubrecipeUpdate);
        $authManager->addChild($roleOwner, $permissionSubrecipeDelete);
        $authManager->addChild($roleOwner, $permissionRecipeList);
        $authManager->addChild($roleOwner, $permissionRecipeView);
        $authManager->addChild($roleOwner, $permissionRecipeCreate);
        $authManager->addChild($roleOwner, $permissionRecipeUpdate);
        $authManager->addChild($roleOwner, $permissionRecipeDelete);
        $authManager->addChild($roleOwner, $permissionConvoyList);
        $authManager->addChild($roleOwner, $permissionConvoyView);
        $authManager->addChild($roleOwner, $permissionConvoyCreate);
        $authManager->addChild($roleOwner, $permissionConvoyUpdate);
        $authManager->addChild($roleOwner, $permissionConvoyDelete);
        $authManager->addChild($roleOwner, $permissionComboList);
        $authManager->addChild($roleOwner, $permissionComboView);
        $authManager->addChild($roleOwner, $permissionComboCreate);
        $authManager->addChild($roleOwner, $permissionComboUpdate);
        $authManager->addChild($roleOwner, $permissionComboDelete);
        $authManager->addChild($roleOwner, $permissionPriceTrendView);
        $authManager->addChild($roleOwner, $permissionChartsView);
        $authManager->addChild($roleOwner, $permissionIngredientsList);
        $authManager->addChild($roleOwner, $permissionIngredientsView);
        $authManager->addChild($roleOwner, $permissionIngredientsCreate);
        $authManager->addChild($roleOwner, $permissionIngredientsUpdate);
        $authManager->addChild($roleOwner, $permissionIngredientsDelete);
        $authManager->addChild($roleOwner, $permissionProvidersList);
        $authManager->addChild($roleOwner, $permissionProvidersView);
        $authManager->addChild($roleOwner, $permissionProvidersCreate);
        $authManager->addChild($roleOwner, $permissionProvidersUpdate);
        $authManager->addChild($roleOwner, $permissionProvidersDelete);
        $authManager->addChild($roleOwner, $permissionMenuView);
        $authManager->addChild($roleOwner, $permissionMenuUpdate);
        $authManager->addChild($roleOwner, $permissionMenuAddItem);
        $authManager->addChild($roleOwner, $permissionMenuRemoveItem);
        $authManager->addChild($roleOwner, $permissionProfitabilityView);
        $authManager->addChild($roleOwner, $permissionTheoreticalProfitabilityView);
        $authManager->addChild($roleOwner, $permissionRealProfitabilityView);
        $authManager->addChild($roleOwner, $permissionMenuAnalysisView);
        $authManager->addChild($roleOwner, $permissionMenuAnalysisUpdate);
        $authManager->addChild($roleOwner, $permissionMenuImprovementsView);
        $authManager->addChild($roleOwner, $permissionMenuImprovementsUpdate);
        $authManager->addChild($roleOwner, $permissionAbcAnalysisView);
        $authManager->addChild($roleOwner, $permissionMovementsList);
        $authManager->addChild($roleOwner, $permissionMovementsView);
        $authManager->addChild($roleOwner, $permissionMovementsCreate);
        $authManager->addChild($roleOwner, $permissionMovementsUpdate);
        $authManager->addChild($roleOwner, $permissionMovementsDelete);

        // Role Administrator
        $authManager->removeChildren($roleAdministrator);
        $authManager->addChild($roleAdministrator, $permissionStorageList);
        $authManager->addChild($roleAdministrator, $permissionStorageView);
        $authManager->addChild($roleAdministrator, $permissionSubrecipeList);
        $authManager->addChild($roleAdministrator, $permissionSubrecipeView);
        $authManager->addChild($roleAdministrator, $permissionSubrecipeCreate);
        $authManager->addChild($roleAdministrator, $permissionSubrecipeUpdate);
        $authManager->addChild($roleAdministrator, $permissionSubrecipeDelete);
        $authManager->addChild($roleAdministrator, $permissionRecipeList);
        $authManager->addChild($roleAdministrator, $permissionRecipeView);
        $authManager->addChild($roleAdministrator, $permissionRecipeCreate);
        $authManager->addChild($roleAdministrator, $permissionRecipeUpdate);
        $authManager->addChild($roleAdministrator, $permissionRecipeDelete);
        $authManager->addChild($roleAdministrator, $permissionConvoyList);
        $authManager->addChild($roleAdministrator, $permissionConvoyView);
        $authManager->addChild($roleAdministrator, $permissionConvoyCreate);
        $authManager->addChild($roleAdministrator, $permissionConvoyUpdate);
        $authManager->addChild($roleAdministrator, $permissionConvoyDelete);
        $authManager->addChild($roleAdministrator, $permissionComboList);
        $authManager->addChild($roleAdministrator, $permissionComboView);
        $authManager->addChild($roleAdministrator, $permissionComboCreate);
        $authManager->addChild($roleAdministrator, $permissionComboUpdate);
        $authManager->addChild($roleAdministrator, $permissionComboDelete);
        $authManager->addChild($roleAdministrator, $permissionPriceTrendView);
        $authManager->addChild($roleAdministrator, $permissionChartsView);
        $authManager->addChild($roleAdministrator, $permissionIngredientsList);
        $authManager->addChild($roleAdministrator, $permissionIngredientsView);
        $authManager->addChild($roleAdministrator, $permissionIngredientsCreate);
        $authManager->addChild($roleAdministrator, $permissionIngredientsUpdate);
        $authManager->addChild($roleAdministrator, $permissionIngredientsDelete);
        $authManager->addChild($roleAdministrator, $permissionProvidersList);
        $authManager->addChild($roleAdministrator, $permissionProvidersView);
        $authManager->addChild($roleAdministrator, $permissionProvidersCreate);
        $authManager->addChild($roleAdministrator, $permissionProvidersUpdate);
        $authManager->addChild($roleAdministrator, $permissionProvidersDelete);
        $authManager->addChild($roleAdministrator, $permissionMenuView);
        $authManager->addChild($roleAdministrator, $permissionMenuUpdate);
        $authManager->addChild($roleAdministrator, $permissionMenuAddItem);
        $authManager->addChild($roleAdministrator, $permissionMenuRemoveItem);
        $authManager->addChild($roleAdministrator, $permissionProfitabilityView);
        $authManager->addChild($roleAdministrator, $permissionTheoreticalProfitabilityView);
        $authManager->addChild($roleAdministrator, $permissionRealProfitabilityView);
        $authManager->addChild($roleAdministrator, $permissionMenuAnalysisView);
        $authManager->addChild($roleAdministrator, $permissionMenuAnalysisUpdate);
        $authManager->addChild($roleAdministrator, $permissionMenuImprovementsView);
        $authManager->addChild($roleAdministrator, $permissionMenuImprovementsUpdate);
        $authManager->addChild($roleAdministrator, $permissionAbcAnalysisView);
        $authManager->addChild($roleAdministrator, $permissionMovementsList);
        $authManager->addChild($roleAdministrator, $permissionMovementsView);
        $authManager->addChild($roleAdministrator, $permissionMovementsCreate);
        $authManager->addChild($roleAdministrator, $permissionMovementsUpdate);
        $authManager->addChild($roleAdministrator, $permissionMovementsDelete);

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
        $roleGeneralManager = $authManager->createRole('general_manager');
        $roleGeneralManager->description = 'Gerente General';
        $authManager->add($roleGeneralManager);

        $roleSousChef = $authManager->createRole('sous_chef');
        $roleSousChef->description = 'Sous Chef';
        $authManager->add($roleSousChef);

        $rolePurchasingManager = $authManager->createRole('purchasing_manager');
        $rolePurchasingManager->description = 'Encargado de compras';
        $authManager->add($rolePurchasingManager);

        $roleMaintenanceChief = $authManager->createRole('maintenance_chief');
        $roleMaintenanceChief->description = 'Jefe de Mantenimiento';
        $authManager->add($roleMaintenanceChief);

        $roleWaiterCaptain = $authManager->createRole('waiter_captain');
        $roleWaiterCaptain->description = 'Capitán de Meseros';
        $authManager->add($roleWaiterCaptain);

        $roleAccountant = $authManager->createRole('accountant');
        $roleAccountant->description = 'Contador';
        $authManager->add($roleAccountant);

        $roleWarehouseAssistant = $authManager->createRole('warehouse_assistant');
        $roleWarehouseAssistant->description = 'Asistente de Almacén';
        $authManager->add($roleWarehouseAssistant);

        $roleExternalConsultant = $authManager->createRole('external_consultant');
        $roleExternalConsultant->description = 'Consultor Externo';
        $authManager->add($roleExternalConsultant);

        $roleAdministrativeAssistant = $authManager->createRole('administrative_assistant');
        $roleAdministrativeAssistant->description = 'Asistente Administrativo';
        $authManager->add($roleAdministrativeAssistant);

        $roleMenuManager = $authManager->createRole('menu_manager');
        $roleMenuManager->description = 'Gerente de Menú';
        $authManager->add($roleMenuManager);

        // Assign permissions to General Manager
        $authManager->addChild($roleGeneralManager, $permissionDashboardView);
        $authManager->addChild($roleGeneralManager, $permissionUsersList);
        $authManager->addChild($roleGeneralManager, $permissionUsersView);
        $authManager->addChild($roleGeneralManager, $permissionUsersCreate);
        $authManager->addChild($roleGeneralManager, $permissionUsersUpdate);
        $authManager->addChild($roleGeneralManager, $permissionUsersDelete);
        $authManager->addChild($roleGeneralManager, $permissionRolesList);
        $authManager->addChild($roleGeneralManager, $permissionRolesView);
        $authManager->addChild($roleGeneralManager, $permissionRolesCreate);
        $authManager->addChild($roleGeneralManager, $permissionRolesUpdate);
        $authManager->addChild($roleGeneralManager, $permissionRolesDelete);
        $authManager->addChild($roleGeneralManager, $permissionRecipeList);
        $authManager->addChild($roleGeneralManager, $permissionRecipeView);
        $authManager->addChild($roleGeneralManager, $permissionRecipeCreate);
        $authManager->addChild($roleGeneralManager, $permissionRecipeUpdate);
        $authManager->addChild($roleGeneralManager, $permissionRecipeDelete);
        $authManager->addChild($roleGeneralManager, $permissionIngredientsList);
        $authManager->addChild($roleGeneralManager, $permissionIngredientsView);
        $authManager->addChild($roleGeneralManager, $permissionIngredientsCreate);
        $authManager->addChild($roleGeneralManager, $permissionIngredientsUpdate);
        $authManager->addChild($roleGeneralManager, $permissionIngredientsDelete);
        $authManager->addChild($roleGeneralManager, $permissionProvidersList);
        $authManager->addChild($roleGeneralManager, $permissionProvidersView);
        $authManager->addChild($roleGeneralManager, $permissionProvidersCreate);
        $authManager->addChild($roleGeneralManager, $permissionProvidersUpdate);
        $authManager->addChild($roleGeneralManager, $permissionProvidersDelete);
        $authManager->addChild($roleGeneralManager, $permissionInventoryList);
        $authManager->addChild($roleGeneralManager, $permissionInventoryView);
        $authManager->addChild($roleGeneralManager, $permissionInventoryCreate);
        $authManager->addChild($roleGeneralManager, $permissionInventoryUpdate);
        $authManager->addChild($roleGeneralManager, $permissionInventoryDelete);
        $authManager->addChild($roleGeneralManager, $permissionMovementsList);
        $authManager->addChild($roleGeneralManager, $permissionMovementsView);
        $authManager->addChild($roleGeneralManager, $permissionMovementsCreate);
        $authManager->addChild($roleGeneralManager, $permissionMovementsUpdate);
        $authManager->addChild($roleGeneralManager, $permissionMovementsDelete);

        // Assign permissions to Sous Chef
        $authManager->addChild($roleSousChef, $permissionDashboardView);
        $authManager->addChild($roleSousChef, $permissionRecipeList);
        $authManager->addChild($roleSousChef, $permissionRecipeView);
        $authManager->addChild($roleSousChef, $permissionRecipeCreate);
        $authManager->addChild($roleSousChef, $permissionRecipeUpdate);
        $authManager->addChild($roleSousChef, $permissionRecipeDelete);
        $authManager->addChild($roleSousChef, $permissionIngredientsList);
        $authManager->addChild($roleSousChef, $permissionIngredientsView);
        $authManager->addChild($roleSousChef, $permissionIngredientsCreate);
        $authManager->addChild($roleSousChef, $permissionIngredientsUpdate);
        $authManager->addChild($roleSousChef, $permissionIngredientsDelete);
        $authManager->addChild($roleSousChef, $permissionInventoryList);
        $authManager->addChild($roleSousChef, $permissionInventoryView);
        $authManager->addChild($roleSousChef, $permissionInventoryCreate);
        $authManager->addChild($roleSousChef, $permissionInventoryUpdate);
        $authManager->addChild($roleSousChef, $permissionInventoryDelete);
        $authManager->addChild($roleSousChef, $permissionMovementsList);
        $authManager->addChild($roleSousChef, $permissionMovementsView);
        $authManager->addChild($roleSousChef, $permissionMovementsCreate);
        $authManager->addChild($roleSousChef, $permissionMovementsUpdate);
        $authManager->addChild($roleSousChef, $permissionMovementsDelete);

        // Assign permissions to Purchasing Manager
        $authManager->addChild($rolePurchasingManager, $permissionDashboardView);
        $authManager->addChild($rolePurchasingManager, $permissionProvidersList);
        $authManager->addChild($rolePurchasingManager, $permissionProvidersView);
        $authManager->addChild($rolePurchasingManager, $permissionProvidersCreate);
        $authManager->addChild($rolePurchasingManager, $permissionProvidersUpdate);
        $authManager->addChild($rolePurchasingManager, $permissionProvidersDelete);
        $authManager->addChild($rolePurchasingManager, $permissionIngredientsList);
        $authManager->addChild($rolePurchasingManager, $permissionIngredientsView);
        $authManager->addChild($rolePurchasingManager, $permissionIngredientsCreate);
        $authManager->addChild($rolePurchasingManager, $permissionIngredientsUpdate);
        $authManager->addChild($rolePurchasingManager, $permissionIngredientsDelete);
        $authManager->addChild($rolePurchasingManager, $permissionInventoryList);
        $authManager->addChild($rolePurchasingManager, $permissionInventoryView);
        $authManager->addChild($rolePurchasingManager, $permissionInventoryCreate);
        $authManager->addChild($rolePurchasingManager, $permissionInventoryUpdate);
        $authManager->addChild($rolePurchasingManager, $permissionInventoryDelete);
        $authManager->addChild($rolePurchasingManager, $permissionMovementsList);
        $authManager->addChild($rolePurchasingManager, $permissionMovementsView);
        $authManager->addChild($rolePurchasingManager, $permissionMovementsCreate);
        $authManager->addChild($rolePurchasingManager, $permissionMovementsUpdate);
        $authManager->addChild($rolePurchasingManager, $permissionMovementsDelete);

        // Assign permissions to Maintenance Chief
        $authManager->addChild($roleMaintenanceChief, $permissionDashboardView);
        $authManager->addChild($roleMaintenanceChief, $permissionInventoryList);
        $authManager->addChild($roleMaintenanceChief, $permissionInventoryView);
        $authManager->addChild($roleMaintenanceChief, $permissionInventoryCreate);
        $authManager->addChild($roleMaintenanceChief, $permissionInventoryUpdate);
        $authManager->addChild($roleMaintenanceChief, $permissionInventoryDelete);
        $authManager->addChild($roleMaintenanceChief, $permissionMovementsList);
        $authManager->addChild($roleMaintenanceChief, $permissionMovementsView);
        $authManager->addChild($roleMaintenanceChief, $permissionMovementsCreate);
        $authManager->addChild($roleMaintenanceChief, $permissionMovementsUpdate);
        $authManager->addChild($roleMaintenanceChief, $permissionMovementsDelete);

        // Assign permissions to Waiter Captain
        $authManager->addChild($roleWaiterCaptain, $permissionDashboardView);
        $authManager->addChild($roleWaiterCaptain, $permissionRecipeList);
        $authManager->addChild($roleWaiterCaptain, $permissionRecipeView);
        $authManager->addChild($roleWaiterCaptain, $permissionInventoryList);
        $authManager->addChild($roleWaiterCaptain, $permissionInventoryView);
        $authManager->addChild($roleWaiterCaptain, $permissionMovementsList);
        $authManager->addChild($roleWaiterCaptain, $permissionMovementsView);

        // Assign permissions to Accountant
        $authManager->addChild($roleAccountant, $permissionDashboardView);
        $authManager->addChild($roleAccountant, $permissionProvidersList);
        $authManager->addChild($roleAccountant, $permissionProvidersView);
        $authManager->addChild($roleAccountant, $permissionInventoryList);
        $authManager->addChild($roleAccountant, $permissionInventoryView);
        $authManager->addChild($roleAccountant, $permissionMovementsList);
        $authManager->addChild($roleAccountant, $permissionMovementsView);
        $authManager->addChild($roleAccountant, $permissionMovementsCreate);
        $authManager->addChild($roleAccountant, $permissionMovementsUpdate);
        $authManager->addChild($roleAccountant, $permissionMovementsDelete);

        // Assign permissions to Warehouse Assistant
        $authManager->addChild($roleWarehouseAssistant, $permissionDashboardView);
        $authManager->addChild($roleWarehouseAssistant, $permissionInventoryList);
        $authManager->addChild($roleWarehouseAssistant, $permissionInventoryView);
        $authManager->addChild($roleWarehouseAssistant, $permissionInventoryCreate);
        $authManager->addChild($roleWarehouseAssistant, $permissionInventoryUpdate);
        $authManager->addChild($roleWarehouseAssistant, $permissionInventoryDelete);
        $authManager->addChild($roleWarehouseAssistant, $permissionMovementsList);
        $authManager->addChild($roleWarehouseAssistant, $permissionMovementsView);
        $authManager->addChild($roleWarehouseAssistant, $permissionMovementsCreate);
        $authManager->addChild($roleWarehouseAssistant, $permissionMovementsUpdate);
        $authManager->addChild($roleWarehouseAssistant, $permissionMovementsDelete);

        // Assign permissions to External Consultant
        $authManager->addChild($roleExternalConsultant, $permissionDashboardView);
        $authManager->addChild($roleExternalConsultant, $permissionRecipeList);
        $authManager->addChild($roleExternalConsultant, $permissionRecipeView);
        $authManager->addChild($roleExternalConsultant, $permissionIngredientsList);
        $authManager->addChild($roleExternalConsultant, $permissionIngredientsView);
        $authManager->addChild($roleExternalConsultant, $permissionProvidersList);
        $authManager->addChild($roleExternalConsultant, $permissionProvidersView);
        $authManager->addChild($roleExternalConsultant, $permissionInventoryList);
        $authManager->addChild($roleExternalConsultant, $permissionInventoryView);
        $authManager->addChild($roleExternalConsultant, $permissionMovementsList);
        $authManager->addChild($roleExternalConsultant, $permissionMovementsView);

        // Assign permissions to Administrative Assistant
        $authManager->addChild($roleAdministrativeAssistant, $permissionDashboardView);
        $authManager->addChild($roleAdministrativeAssistant, $permissionUsersList);
        $authManager->addChild($roleAdministrativeAssistant, $permissionUsersView);
        $authManager->addChild($roleAdministrativeAssistant, $permissionUsersCreate);
        $authManager->addChild($roleAdministrativeAssistant, $permissionUsersUpdate);
        $authManager->addChild($roleAdministrativeAssistant, $permissionUsersDelete);
        $authManager->addChild($roleAdministrativeAssistant, $permissionRecipeList);
        $authManager->addChild($roleAdministrativeAssistant, $permissionRecipeView);
        $authManager->addChild($roleAdministrativeAssistant, $permissionIngredientsList);
        $authManager->addChild($roleAdministrativeAssistant, $permissionIngredientsView);
        $authManager->addChild($roleAdministrativeAssistant, $permissionProvidersList);
        $authManager->addChild($roleAdministrativeAssistant, $permissionProvidersView);
        $authManager->addChild($roleAdministrativeAssistant, $permissionInventoryList);
        $authManager->addChild($roleAdministrativeAssistant, $permissionInventoryView);
        $authManager->addChild($roleAdministrativeAssistant, $permissionMovementsList);
        $authManager->addChild($roleAdministrativeAssistant, $permissionMovementsView);

        // Assign permissions to Menu Manager
        $authManager->addChild($roleMenuManager, $permissionDashboardView);
        $authManager->addChild($roleMenuManager, $permissionRecipeList);
        $authManager->addChild($roleMenuManager, $permissionRecipeView);
        $authManager->addChild($roleMenuManager, $permissionRecipeCreate);
        $authManager->addChild($roleMenuManager, $permissionRecipeUpdate);
        $authManager->addChild($roleMenuManager, $permissionRecipeDelete);
        $authManager->addChild($roleMenuManager, $permissionIngredientsList);
        $authManager->addChild($roleMenuManager, $permissionIngredientsView);
        $authManager->addChild($roleMenuManager, $permissionIngredientsCreate);
        $authManager->addChild($roleMenuManager, $permissionIngredientsUpdate);
        $authManager->addChild($roleMenuManager, $permissionIngredientsDelete);

    }


}
