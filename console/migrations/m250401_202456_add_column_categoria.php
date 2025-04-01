<?php

use yii\db\Migration;

class m250401_202456_add_column_categoria extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('menu_bundle_product', 'categoria', $this->string()->after('rentabilidad_real'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250401_202456_add_column_categoria cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250401_202456_add_column_categoria cannot be reverted.\n";

        return false;
    }
    */
}
