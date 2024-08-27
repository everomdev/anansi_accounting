<?php

use yii\db\Migration;

/**
 * Class m240827_191730_add_um_to_standard_recipe
 */
class m240827_191730_add_um_to_standard_recipe extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('standard_recipe', 'um', $this->string(255));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('standard_recipe', 'um');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240827_191730_add_um_to_standard_recipe cannot be reverted.\n";

        return false;
    }
    */
}
