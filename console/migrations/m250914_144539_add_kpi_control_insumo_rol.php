<?php

use yii\db\Migration;

/**
 * Migration for adding KPI control insumo role and permission.
 */
class m250914_144539_add_kpi_control_insumo_rol extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = \Yii::$app->authManager;

        // Crear permiso si no existe
        if (!$auth->getPermission('kpi_access')) {
            $kpiAccess = $auth->createPermission('kpi_access');
            $kpiAccess->description = 'Acceso a módulo KPI';
            $auth->add($kpiAccess);
        } else {
            $kpiAccess = $auth->getPermission('kpi_access');
        }

        // Crear rol si no existe
        if (!$auth->getRole('kpi')) {
            $kpiRole = $auth->createRole('kpi');
            $kpiRole->description = 'Acceso a KPI';
            $auth->add($kpiRole);
        } else {
            $kpiRole = $auth->getRole('kpi');
        }

        // Asignar permiso al rol si no está asignado
        $children = $auth->getChildren($kpiRole->name);
        if (!isset($children[$kpiAccess->name])) {
            $auth->addChild($kpiRole, $kpiAccess);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250914_144539_add_kpi_control_insumo_rol cannot be reverted.\n";
        return false;
    }
}
