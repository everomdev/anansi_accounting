<?php

use yii\db\Migration;

class m250922_213236_update_type_UM extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
         $this->alterColumn('unit_of_measurement', 'type', $this->string(20)->null());

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250922_213236_update_type_UM cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250922_213236_update_type_UM cannot be reverted.\n";

        return false;
    }
    */
}
