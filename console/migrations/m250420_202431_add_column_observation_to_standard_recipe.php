<?php

use yii\db\Migration;

class m250420_202431_add_column_observation_to_standard_recipe extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('standard_recipe', 'observation', $this->text()->null()->defaultValue(null)->after('is_food'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250420_202431_add_column_observation_to_standard_recipe cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250420_202431_add_column_observation_to_standard_recipe cannot be reverted.\n";

        return false;
    }
    */
}
