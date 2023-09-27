<?php

use yii\db\Migration;

/**
 * Class m230908_134925_add_convoy_id_to_standard_recipe_table
 */
class m230908_134925_add_convoy_id_to_standard_recipe_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('standard_recipe', 'convoy_id', $this->integer());
        $this->createIndex('idx-convoy_id-standard_recipe', 'standard_recipe', 'convoy_id');
        $this->addForeignKey('fk-convoy_id-standard_recipe', 'standard_recipe', 'convoy_id', 'convoy', 'id', 'SET NULL');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('idx-convoy_id-standard_recipe', 'standard_recipe');
        $this->dropIndex('fk-convoy_id-standard_recipe', 'standard_recipe');
        $this->dropColumn('standard_recipe', 'convoy_id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230908_134925_add_convoy_id_to_standard_recipe_table cannot be reverted.\n";

        return false;
    }
    */
}
