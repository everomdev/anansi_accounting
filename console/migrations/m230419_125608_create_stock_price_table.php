<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%stock_price}}`.
 */
class m230419_125608_create_stock_price_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%stock_price}}', [
            'id' => $this->primaryKey(),
            'stock_id' => $this->integer()->notNull(),
            'price' => $this->float()->notNull(),
            'date' => $this->date(),
            'unit_price' => $this->float(),
            'unit_price_yield' => $this->float()
        ]);

        $this->createIndex(
            "idx-stock_id-stock_price",
            "stock_price",
            "stock_id"
        );

        $this->addForeignKey(
            "fk-stock_id-stock_price",
            "stock_price",
            "stock_id",
            "ingredient_stock",
            "id",
            "CASCADE"
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey("fk-stock_id-stock_price", "stock_price");
        $this->dropIndex("idx-stock_id-stock_price", "stock_price");
        $this->dropTable('{{%stock_price}}');
    }
}
