<?php

use yii\db\Migration;

class m250419_195950_planID_column_coupon extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%coupon}}', 'plan_id', $this->integer()->null()->after('type'));
        $this->addForeignKey('fk-coupon-plan_id', '{{%coupon}}', 'plan_id', '{{%plan}}', 'id', 'SET NULL', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250419_195950_planID_column_coupon cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250419_195950_planID_column_coupon cannot be reverted.\n";

        return false;
    }
    */
}
