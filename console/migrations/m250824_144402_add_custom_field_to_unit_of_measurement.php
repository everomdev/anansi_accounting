<?php

use yii\db\Migration;

class m250824_144402_add_custom_field_to_unit_of_measurement extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%unit_of_measurement}}', 'custom', $this->integer()->notNull()->defaultValue(0)->comment('Flag to indicate if this is a custom unit (1) or standard unit (0)'));
        $this->createIndex('idx-unit_of_measurement-custom', '{{%unit_of_measurement}}', 'custom');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-unit_of_measurement-custom', '{{%unit_of_measurement}}');
        $this->dropColumn('{{%unit_of_measurement}}', 'custom');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250824_144402_add_custom_field_to_unit_of_measurement cannot be reverted.\n";

        return false;
    }
    */
}
