<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%release}}`.
 */
class m230523_201222_create_release_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%release}}', [
            'id' => $this->primaryKey(),
            'stock_id' => $this->integer()->notNull(),
            'quantity' => $this->float()->notNull(),
            'date' => $this->date()->notNull(),
            'observations' => $this->text()
        ]);

        $this->createIndex("idx-stock_id-release", "release", "stock_id");
        $this->createIndex("idx-date-release", "release", "date");
        $this->createIndex("idx-quantity-release", "release", "quantity");

        $this->addForeignKey("fk-stock_id-release", "release", "stock_id", "ingredient_stock", "id", "CASCADE");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey("fk-stock_id-release", "release");
        $this->dropForeignKey("idx-stock_id-release", "release");
        $this->dropForeignKey("idx-date-release", "release");
        $this->dropForeignKey("idx-quantity-release", "release");
        $this->dropTable('{{%release}}');
    }
}
