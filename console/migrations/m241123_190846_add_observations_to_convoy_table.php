<?php

use yii\db\Migration;

/**
 * Class m241123_190846_add_observations_to_convoy_table
 */
class m241123_190846_add_observations_to_convoy_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('convoy', 'observations', $this->text());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('convoy', 'observations');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m241123_190846_add_observations_to_convoy_table cannot be reverted.\n";

        return false;
    }
    */
}
