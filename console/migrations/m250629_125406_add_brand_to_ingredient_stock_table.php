<?php

use yii\db\Migration;

class m250629_125406_add_brand_to_ingredient_stock_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%ingredient_stock}}', 'brand', $this->string(255)->null()->comment('Marca del insumo'));
        $this->addColumn('{{%ingredient_stock}}', 'presentation', $this->string(255)->null()->comment('Presentación del insumo'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%ingredient_stock}}', 'presentation');
        $this->dropColumn('{{%ingredient_stock}}', 'brand');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250629_125406_add_brand_to_ingredient_stock_table cannot be reverted.\n";

        return false;
    }
    */
}
