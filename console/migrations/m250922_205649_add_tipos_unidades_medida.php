<?php

use yii\db\Migration;

class m250922_205649_add_tipos_unidades_medida extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('unit_of_measurement', 'is_purchase', $this->boolean()->notNull()->defaultValue(false)->after('type'));
        $this->addColumn('unit_of_measurement', 'is_kitchen', $this->boolean()->notNull()->defaultValue(false)->after('is_purchase'));
        $this->addColumn('unit_of_measurement', 'is_subrecipe_yield', $this->boolean()->notNull()->defaultValue(false)->after('is_kitchen'));
        $this->addColumn('unit_of_measurement', 'is_subrecipe_um', $this->boolean()->notNull()->defaultValue(false)->after('is_subrecipe_yield'));
        $this->addColumn('unit_of_measurement', 'is_recipe_yield', $this->boolean()->notNull()->defaultValue(false)->after('is_subrecipe_um'));
        $this->addColumn('unit_of_measurement', 'is_recipe_final_um', $this->boolean()->notNull()->defaultValue(false)->after('is_recipe_yield'));
    }

    public function safeDown()
    {
        $this->dropColumn('unit_of_measurement', 'is_recipe_final_um');
        $this->dropColumn('unit_of_measurement', 'is_recipe_yield');
        $this->dropColumn('unit_of_measurement', 'is_subrecipe_um');
        $this->dropColumn('unit_of_measurement', 'is_subrecipe_yield');
        $this->dropColumn('unit_of_measurement', 'is_kitchen');
        $this->dropColumn('unit_of_measurement', 'is_purchase');
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250922_205649_add_tipos_unidades_medida cannot be reverted.\n";

        return false;
    }
    */
}
