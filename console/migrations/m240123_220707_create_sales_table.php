<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%sales}}`.
 */
class m240123_220707_create_sales_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%sales}}', [
            'id' => $this->primaryKey(),
            'date' => $this->date(),
            'amount_food' => $this->float(),
            'amount_drinking' => $this->float(),
            'amount_other' => $this->float(),
            'business_id' => $this->integer()->notNull()
        ]);

        $this->createIndex('idx-business_id-sales', 'sales', 'business_id');
        $this->addForeignKey('fk-business_id-sales', 'sales', 'business_id', 'business', 'id', 'CASCADE');


    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-business_id-sales', 'sales');
        $this->dropIndex('idx-business_id-sales', 'sales');
        $this->dropTable('{{%sales}}');
    }
}
