<?php

use yii\db\Migration;

/**
 * Class m230907_064736_alter_provider_table
 */
class m230907_064736_alter_provider_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('provider', 'payment_method', $this->string());
        $this->addColumn('provider', 'account', $this->string());
        $this->addColumn('provider', 'credit_days', $this->string());
        $this->addColumn('provider', 'rfc', $this->string());
        $this->addColumn('provider', 'business_name', $this->string());
        $this->addColumn('provider', 'advantages', $this->string());
        $this->addColumn('provider', 'disadvantages', $this->string());
        $this->addColumn('provider', 'observations', $this->string());
        $this->dropColumn('provider', 'fax');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('provider', 'fax', $this->string());
        $this->dropColumn('provider', 'payment_method');
        $this->dropColumn('provider', 'account');
        $this->dropColumn('provider', 'credit_days');
        $this->dropColumn('provider', 'rfc');
        $this->dropColumn('provider', 'business_name');
        $this->dropColumn('provider', 'advantages');
        $this->dropColumn('provider', 'disadvantages');
        $this->dropColumn('provider', 'observations');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230907_064736_alter_provider_table cannot be reverted.\n";

        return false;
    }
    */
}
