<?php

use yii\db\Migration;

/**
 * Class m230925_182152_alter_plan_table
 */
class m230925_182152_alter_plan_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('plan', 'subrecetas', $this->integer()->defaultValue(25));
        $this->addColumn('plan', 'recetas', $this->integer()->defaultValue(50));
        $this->addColumn('plan', 'convoy', $this->integer()->defaultValue(1));
        $this->addColumn('plan', 'combos', $this->integer()->defaultValue(20));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('plan', 'subrecetas');
        $this->dropColumn('plan', 'recetas');
        $this->dropColumn('plan', 'convoy');
        $this->dropColumn('plan', 'combos');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230925_182152_alter_plan_table cannot be reverted.\n";

        return false;
    }
    */
}
