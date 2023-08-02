<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%purchase}}`.
 */
class m230419_125547_create_purchase_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%purchase}}', [
            'id' => $this->primaryKey(),
            'date' => $this->date()->notNull(),
            'price' => $this->float(),
            'provider' => $this->string(),
            'quantity' => $this->float(),
            'stock_id' => $this->integer()->notNull(),
            'um' => $this->string(),
            'final_um' => $this->string()
        ]);

        $this->createIndex(
            "idx-stock_id-purchase",
            "purchase",
            "stock_id"
        );

        $this->addForeignKey(
            "fk-stock_id-purchase",
            "purchase",
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
        $this->dropForeignKey("fk-stock_id-purchase", "purchase");
        $this->dropIndex("idx-stock_id-purchase", "purchase");
        $this->dropTable('{{%purchase}}');
    }
}
