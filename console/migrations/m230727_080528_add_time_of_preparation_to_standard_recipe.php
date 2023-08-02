<?php

use yii\db\Migration;

/**
 * Class m230727_080528_add_time_of_preparation_to_standard_recipe
 */
class m230727_080528_add_time_of_preparation_to_standard_recipe extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('standard_recipe', 'time_of_preparation', $this->string());
        $this->addColumn('standard_recipe', 'yield', $this->float());
        $this->addColumn('standard_recipe', 'yield_um', $this->string());
        $this->addColumn('standard_recipe', 'portions', $this->float());
        $this->addColumn('standard_recipe', 'lifetime', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('standard_recipe', 'time_of_preparation');
        $this->dropColumn('standard_recipe', 'yield');
        $this->dropColumn('standard_recipe', 'yield_um');
        $this->dropColumn('standard_recipe', 'portions');
        $this->dropColumn('standard_recipe', 'lifetime');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230727_080528_add_time_of_preparation_to_standard_recipe cannot be reverted.\n";

        return false;
    }
    */
}
