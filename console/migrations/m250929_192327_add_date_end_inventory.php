<?php

use yii\db\Migration;

class m250929_192327_add_date_end_inventory extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('inventory', 'date_end', $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250929_192327_add_date_end_inventory cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250929_192327_add_date_end_inventory cannot be reverted.\n";

        return false;
    }
    */
}
