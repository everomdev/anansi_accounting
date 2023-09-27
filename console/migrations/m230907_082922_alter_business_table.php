<?php

use yii\db\Migration;

/**
 * Class m230907_082922_alter_business_table
 */
class m230907_082922_alter_business_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('business', 'currency_code', $this->string(3)->defaultValue('mxn')->notNull());
        $this->addColumn('business', 'decimal_separator', $this->string(1)->defaultValue(',')->notNull());
        $this->addColumn('business', 'thousands_separator', $this->string(1)->defaultValue('.')->notNull());
        $this->addColumn('business', 'timezone', $this->string()->defaultValue('America/Mexico_City')->notNull());
        $this->addColumn('business', 'locale', $this->string()->defaultValue('es_MX')->notNull());

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('business', 'currency_code');
        $this->dropColumn('business', 'decimal_separator');
        $this->dropColumn('business', 'thousands_separator');
        $this->dropColumn('business', 'timezone');
        $this->dropColumn('business', 'locale');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230907_082922_alter_business_table cannot be reverted.\n";

        return false;
    }
    */
}
