<?php

namespace console\controllers;

use backend\rules\PaymentRule;
use yii\console\Controller;

class RbacController extends Controller
{
    public function actionInit()
    {
        $authManager = \Yii::$app->authManager;
        $authManager->removeAll();

        $paymentRule = new PaymentRule();
        $authManager->add($paymentRule);
        // create roles
        // Role Admin
        $roleAdmin = $authManager->createRole('admin');
        $roleAdmin->description = "Administrador del sistema";

        // Role Storage
        $roleStorage = $authManager->createRole('storage');
        $roleStorage->description = "Almacén";

        // Role Storage Admin
        $roleStorageAdmin = $authManager->createRole('storage_admin');
        $roleStorageAdmin->description = "Administrador de Almacén";

        // Role Chef
        $roleChef = $authManager->createRole('chef');
        $roleChef->description = "Chef";

        // Role Executive Chef
        $roleExecutiveChef = $authManager->createRole('executive_chef');
        $roleExecutiveChef->description = "Chef Ejecutivo";

        // Role Owner
        $roleOwner = $authManager->createRole('owner');
        $roleOwner->description = "Propietario";

        // Role Administrator
        $roleAdministrator = $authManager->createRole('administrator');
        $roleAdministrator->description = "Administrador";

        // Add roles to auth manager
        $authManager->add($roleAdmin);
        $authManager->add($roleStorage);
        $authManager->add($roleStorageAdmin);
        $authManager->add($roleChef);
        $authManager->add($roleExecutiveChef);
        $authManager->add($roleOwner);
        $authManager->add($roleAdministrator);

        // Create permissions
        // Storage permissions
        $permissionStorageList = $authManager->createPermission('storage_list');
        $permissionStorageView = $authManager->createPermission('storage_view');
//        $permissionStorageCreate = $authManager->createPermission('storage_create');
//        $permissionStorageUpdate = $authManager->createPermission('storage_update');
//        $permissionStorageDelete = $authManager->createPermission('storage_delete');
        $permissionStorageList->description = "Ver almacén";
        $permissionStorageView->description = "Ver detalles de almacén";
        $permissionStorageList->ruleName = $paymentRule->name;
        $permissionStorageView->ruleName = $paymentRule->name;
//        $permissionStorageCreate->description = "Crear un nuevo almacén";
//        $permissionStorageUpdate->description = "Actualizar información del almacén";
//        $permissionStorageDelete->description = "Eliminar un almacén";


        $authManager->add($permissionStorageList);
        $authManager->add($permissionStorageView);
//        $authManager->add($permissionStorageCreate);
//        $authManager->add($permissionStorageUpdate);
//        $authManager->add($permissionStorageDelete);

        // Subrecipe permissions
        $permissionSubrecipeList = $authManager->createPermission('subrecipe_list');
        $permissionSubrecipeView = $authManager->createPermission('subrecipe_view');
        $permissionSubrecipeCreate = $authManager->createPermission('subrecipe_create');
        $permissionSubrecipeUpdate = $authManager->createPermission('subrecipe_update');
        $permissionSubrecipeDelete = $authManager->createPermission('subrecipe_delete');

        $permissionSubrecipeList->description = "Ver lista de subrecetas";
        $permissionSubrecipeView->description = "Ver detalles de subreceta";
        $permissionSubrecipeCreate->description = "Crear una nueva subreceta";
        $permissionSubrecipeUpdate->description = "Actualizar información de subreceta";
        $permissionSubrecipeDelete->description = "Eliminar una subreceta";

        $permissionSubrecipeList->ruleName = $paymentRule->name;
        $permissionSubrecipeView->ruleName = $paymentRule->name;
        $permissionSubrecipeCreate->ruleName = $paymentRule->name;
        $permissionSubrecipeUpdate->ruleName = $paymentRule->name;
        $permissionSubrecipeDelete->ruleName = $paymentRule->name;

        $authManager->add($permissionSubrecipeList);
        $authManager->add($permissionSubrecipeView);
        $authManager->add($permissionSubrecipeCreate);
        $authManager->add($permissionSubrecipeUpdate);
        $authManager->add($permissionSubrecipeDelete);

        // Recipe permissions
        $permissionRecipeList = $authManager->createPermission('recipe_list');
        $permissionRecipeView = $authManager->createPermission('recipe_view');
        $permissionRecipeCreate = $authManager->createPermission('recipe_create');
        $permissionRecipeUpdate = $authManager->createPermission('recipe_update');
        $permissionRecipeDelete = $authManager->createPermission('recipe_delete');

        $permissionRecipeList->description = "Ver lista de recetas";
        $permissionRecipeView->description = "Ver detalles de receta";
        $permissionRecipeCreate->description = "Crear una nueva receta";
        $permissionRecipeUpdate->description = "Actualizar información de receta";
        $permissionRecipeDelete->description = "Eliminar una receta";

        $permissionRecipeList->ruleName = $paymentRule->name;
        $permissionRecipeView->ruleName = $paymentRule->name;
        $permissionRecipeCreate->ruleName = $paymentRule->name;
        $permissionRecipeUpdate->ruleName = $paymentRule->name;
        $permissionRecipeDelete->ruleName = $paymentRule->name;

        $authManager->add($permissionRecipeList);
        $authManager->add($permissionRecipeView);
        $authManager->add($permissionRecipeCreate);
        $authManager->add($permissionRecipeUpdate);
        $authManager->add($permissionRecipeDelete);

        // Convoy permissions
        $permissionConvoyList = $authManager->createPermission('convoy_list');
        $permissionConvoyView = $authManager->createPermission('convoy_view');
        $permissionConvoyCreate = $authManager->createPermission('convoy_create');
        $permissionConvoyUpdate = $authManager->createPermission('convoy_update');
        $permissionConvoyDelete = $authManager->createPermission('convoy_delete');

        $permissionConvoyList->description = "Ver lista de convoyes";
        $permissionConvoyView->description = "Ver detalles de convoy";
        $permissionConvoyCreate->description = "Crear un nuevo convoy";
        $permissionConvoyUpdate->description = "Actualizar información de convoy";
        $permissionConvoyDelete->description = "Eliminar un convoy";

        $permissionConvoyList->ruleName = $paymentRule->name;
        $permissionConvoyView->ruleName = $paymentRule->name;
        $permissionConvoyCreate->ruleName = $paymentRule->name;
        $permissionConvoyUpdate->ruleName = $paymentRule->name;
        $permissionConvoyDelete->ruleName = $paymentRule->name;

        $authManager->add($permissionConvoyList);
        $authManager->add($permissionConvoyView);
        $authManager->add($permissionConvoyCreate);
        $authManager->add($permissionConvoyUpdate);
        $authManager->add($permissionConvoyDelete);

        // Combo permissions
        $permissionComboList = $authManager->createPermission('combo_list');
        $permissionComboView = $authManager->createPermission('combo_view');
        $permissionComboCreate = $authManager->createPermission('combo_create');
        $permissionComboUpdate = $authManager->createPermission('combo_update');
        $permissionComboDelete = $authManager->createPermission('combo_delete');

        $permissionComboList->description = "Ver lista de combos";
        $permissionComboView->description = "Ver detalles de combo";
        $permissionComboCreate->description = "Crear un nuevo combo";
        $permissionComboUpdate->description = "Actualizar información de combo";
        $permissionComboDelete->description = "Eliminar un combo";

        $permissionComboList->ruleName = $paymentRule->name;
        $permissionComboView->ruleName = $paymentRule->name;
        $permissionComboCreate->ruleName = $paymentRule->name;
        $permissionComboUpdate->ruleName = $paymentRule->name;
        $permissionComboDelete->ruleName = $paymentRule->name;

        $authManager->add($permissionComboList);
        $authManager->add($permissionComboView);
        $authManager->add($permissionComboCreate);
        $authManager->add($permissionComboUpdate);
        $authManager->add($permissionComboDelete);

        // Price trend permissions
        $permissionPriceTrendView = $authManager->createPermission('price_trend_view');
        $permissionPriceTrendView->description = "Ver detalles de tendencia de precios";

        $authManager->add($permissionPriceTrendView);

        // Charts permissions
        $permissionChartsView = $authManager->createPermission('charts_view');
        $permissionChartsView->description = "Ver lista de gráficos";
        $permissionChartsView->ruleName = $paymentRule->name;
        $authManager->add($permissionChartsView);

        // Ingredients permissions
        $permissionIngredientsList = $authManager->createPermission('ingredients_list');
        $permissionIngredientsView = $authManager->createPermission('ingredients_view');
        $permissionIngredientsCreate = $authManager->createPermission('ingredients_create');
        $permissionIngredientsUpdate = $authManager->createPermission('ingredients_update');
        $permissionIngredientsDelete = $authManager->createPermission('ingredients_delete');

        $permissionIngredientsList->description = "Ver lista de ingredientes";
        $permissionIngredientsView->description = "Ver detalles de ingredientes";
        $permissionIngredientsCreate->description = "Crear un nuevo ingrediente";
        $permissionIngredientsUpdate->description = "Actualizar información de ingredientes";
        $permissionIngredientsDelete->description = "Eliminar un ingrediente";

        $permissionIngredientsList->ruleName = $paymentRule->name;
        $permissionIngredientsView->ruleName = $paymentRule->name;
        $permissionIngredientsCreate->ruleName = $paymentRule->name;
        $permissionIngredientsUpdate->ruleName = $paymentRule->name;
        $permissionIngredientsDelete->ruleName = $paymentRule->name;

        $authManager->add($permissionIngredientsList);
        $authManager->add($permissionIngredientsView);
        $authManager->add($permissionIngredientsCreate);
        $authManager->add($permissionIngredientsUpdate);
        $authManager->add($permissionIngredientsDelete);

        // Providers permissions
        $permissionProvidersList = $authManager->createPermission('providers_list');
        $permissionProvidersView = $authManager->createPermission('providers_view');
        $permissionProvidersCreate = $authManager->createPermission('providers_create');
        $permissionProvidersUpdate = $authManager->createPermission('providers_update');
        $permissionProvidersDelete = $authManager->createPermission('providers_delete');

        $permissionProvidersList->description = "Ver lista de proveedores";
        $permissionProvidersView->description = "Ver detalles de proveedor";
        $permissionProvidersCreate->description = "Crear un nuevo proveedor";
        $permissionProvidersUpdate->description = "Actualizar información de proveedor";
        $permissionProvidersDelete->description = "Eliminar un proveedor";

        $permissionProvidersList->ruleName = $paymentRule->name;
        $permissionProvidersView->ruleName = $paymentRule->name;
        $permissionProvidersCreate->ruleName = $paymentRule->name;
        $permissionProvidersUpdate->ruleName = $paymentRule->name;
        $permissionProvidersDelete->ruleName = $paymentRule->name;

        $authManager->add($permissionProvidersList);
        $authManager->add($permissionProvidersView);
        $authManager->add($permissionProvidersCreate);
        $authManager->add($permissionProvidersUpdate);
        $authManager->add($permissionProvidersDelete);

        // Menu permissions
        $permissionMenuView = $authManager->createPermission('menu_view');
        $permissionMenuUpdate = $authManager->createPermission('menu_update');
        $permissionMenuAddItem = $authManager->createPermission('menu_add_item');
        $permissionMenuRemoveItem = $authManager->createPermission('menu_remove_item');

        $permissionMenuView->description = "Ver detalles de menú";
        $permissionMenuUpdate->description = "Modificar menú";
        $permissionMenuAddItem->description = "Agregar receta o combo a un menú";
        $permissionMenuRemoveItem->description = "Quitar receta o combo de un menú";

        $permissionMenuView->ruleName = $paymentRule->name;
        $permissionMenuUpdate->ruleName = $paymentRule->name;
        $permissionMenuAddItem->ruleName = $paymentRule->name;
        $permissionMenuRemoveItem->ruleName = $paymentRule->name;

        $authManager->add($permissionMenuView);
        $authManager->add($permissionMenuUpdate);
        $authManager->add($permissionMenuAddItem);
        $authManager->add($permissionMenuRemoveItem);

        // Profitability permissions
        $permissionProfitabilityView = $authManager->createPermission('profitability_view');
        $permissionProfitabilityView->description = "Ver rentabilidad";
        $permissionProfitabilityView->ruleName = $paymentRule->name;
        $authManager->add($permissionProfitabilityView);


        // Theoretical profitability permissions
        $permissionTheoreticalProfitabilityView = $authManager->createPermission('theoretical_profitability_view');
        $permissionTheoreticalProfitabilityView->description = "Ver rentabilidad teórica";
        $permissionTheoreticalProfitabilityView->ruleName = $paymentRule->name;
        $authManager->add($permissionTheoreticalProfitabilityView);

        // Real profitability permissions
        $permissionRealProfitabilityView = $authManager->createPermission('real_profitability_view');
        $permissionRealProfitabilityView->description = "Ver detalles de rentabilidad real";
        $permissionRealProfitabilityView->ruleName = $paymentRule->name;
        $authManager->add($permissionRealProfitabilityView);

        // Menu Analysis permissions

        $permissionMenuAnalysisView = $authManager->createPermission('menu_analysis_view');
        $permissionMenuAnalysisUpdate = $authManager->createPermission('menu_analysis_update');
        $permissionMenuAnalysisView->description = "Ver detalles de análisis de menú";
        $permissionMenuAnalysisUpdate->description = "Actualizar información de análisis de menú";

        $permissionMenuAnalysisView->ruleName = $paymentRule->name;
        $permissionMenuAnalysisUpdate->ruleName = $paymentRule->name;

        $authManager->add($permissionMenuAnalysisView);
        $authManager->add($permissionMenuAnalysisUpdate);

        // Menu Improvements permissions
        $permissionMenuImprovementsView = $authManager->createPermission('menu_improvements_view');
        $permissionMenuImprovementsUpdate = $authManager->createPermission('menu_improvements_update');
        $permissionMenuImprovementsView->description = "Ver detalles de mejoras de menú";
        $permissionMenuImprovementsUpdate->description = "Actualizar información de mejora de menú";

        $permissionMenuImprovementsView->ruleName = $paymentRule->name;
        $permissionMenuImprovementsUpdate->ruleName = $paymentRule->name;

        $authManager->add($permissionMenuImprovementsView);
        $authManager->add($permissionMenuImprovementsUpdate);

        // ABC Analysis permissions
        $permissionAbcAnalysisView = $authManager->createPermission('abc_analysis_view');
        $permissionAbcAnalysisView->description = "Ver análisis ABC";
        $permissionAbcAnalysisView->ruleName = $paymentRule->name;
        $authManager->add($permissionAbcAnalysisView);

        // Movement permissions
        $permissionMovementsList = $authManager->createPermission('movements_list');
        $permissionMovementsView = $authManager->createPermission('movements_view');
        $permissionMovementsCreate = $authManager->createPermission('movements_create');
        $permissionMovementsUpdate = $authManager->createPermission('movements_update');
        $permissionMovementsDelete = $authManager->createPermission('movements_delete');
        $permissionMovementsManageBalance = $authManager->createPermission('movements_manage_balance');
        $permissionMovementsList->description = "Ver lista de movimientos";
        $permissionMovementsView->description = "Ver detalles de movimientos";
        $permissionMovementsCreate->description = "Crear un nuevo movimiento";
        $permissionMovementsUpdate->description = "Actualizar información de movimiento";
        $permissionMovementsDelete->description = "Eliminar un movimiento";
        $permissionMovementsManageBalance->description = "Gestionar saldo inicial";

        $permissionMovementsList->ruleName = $paymentRule->name;
        $permissionMovementsView->ruleName = $paymentRule->name;
        $permissionMovementsCreate->ruleName = $paymentRule->name;
        $permissionMovementsUpdate->ruleName = $paymentRule->name;
        $permissionMovementsDelete->ruleName = $paymentRule->name;
        $permissionMovementsManageBalance->ruleName = $paymentRule->name;

        $authManager->add($permissionMovementsList);
        $authManager->add($permissionMovementsView);
        $authManager->add($permissionMovementsCreate);
        $authManager->add($permissionMovementsUpdate);
        $authManager->add($permissionMovementsDelete);
        $authManager->add($permissionMovementsManageBalance);

        $permissionMatrixBCG = $authManager->createPermission('matrix_bcg');
        $permissionMatrixBCG->description = "Ver matríz BCG";
        $permissionMatrixBCG->ruleName = $paymentRule->name;
        $authManager->add($permissionMatrixBCG);

        $permissionSalesView = $authManager->createPermission('sales_view');
        $permissionSalesUpdate = $authManager->createPermission('sales_update');
        $permissionSalesView->description = "Ver ventas";
        $permissionSalesUpdate->description = "Actualizar ventas";
        $permissionSalesView->ruleName = $paymentRule->name;
        $permissionSalesUpdate->ruleName = $paymentRule->name;
        $authManager->add($permissionSalesView);
        $authManager->add($permissionSalesUpdate);

        $permissionManageUsers = $authManager->createPermission('manage_users');
        $permissionManageUsers->description = "Gestionar usuarios";
        $permissionManageUsers->ruleName = $paymentRule->name;
        $authManager->add($permissionManageUsers);

        $permissionManageAccount = $authManager->createPermission('manage_account');
        $permissionManageAccount->description = "Gestionar cuenta";
        $permissionManageAccount->ruleName = $paymentRule->name;
        $authManager->add($permissionManageAccount);


        // add permissions to roles
        // Role Storage
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

    }


}
