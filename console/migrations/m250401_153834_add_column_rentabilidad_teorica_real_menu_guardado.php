<?php

use yii\db\Migration;

class m250401_153834_add_column_rentabilidad_teorica_real_menu_guardado extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('menu_bundle_product', 'rentabilidad_teorica', $this->decimal(10, 2)->defaultValue(0)->after('entity_type'));
        $this->addColumn('menu_bundle_product', 'rentabilidad_real', $this->decimal(10, 2)->defaultValue(0)->after('rentabilidad_teorica'));

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250401_153834_add_column_rentabilidad_teorica_real_menu_guardado cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250401_153834_add_column_rentabilidad_teorica_real_menu_guardado cannot be reverted.\n";

        return false;
    }
    */
}
