<?php

use yii\db\Migration;

class m250303_174249_add_table_cupon extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%coupon}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'code' => $this->string()->unique(),
            'discount' => $this->float(),
            'quantity' => $this->integer(),
            'expiration' => $this->dateTime(),
            'type' => $this->string()->defaultValue('amount') // amount or percent
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%coupon}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250303_174249_add_table_cupon cannot be reverted.\n";

        return false;
    }
    */
}
