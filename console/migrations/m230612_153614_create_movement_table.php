<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%movement}}`.
 */
class m230612_153614_create_movement_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%movement}}', [
            'id' => $this->primaryKey(),
            'type' => $this->string()->notNull(), // INPUT, OUTPUT, ORDER,
            'provider' => $this->string()->notNull(),
            'payment_type' => $this->string(),
            'invoice' => $this->string(),
            'quantity' => $this->float()->notNull(),
            'um' => $this->string()->notNull(),
            'amount' => $this->float(),
            'tax' => $this->float(),
            'retention' => $this->float(),
            'unit_price' => $this->float(),
            'total' => $this->float(),
            'observations' => $this->string(),
            'ingredient_id' => $this->integer()->notNull(),
            'business_id' => $this->integer()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ]);

        $this->createIndex("idx-ingredient_id-movement", "movement", "ingredient_id");
        $this->createIndex("idx-business_id-movement", "movement", "business_id");
        $this->createIndex("idx-created_at-movement", "movement", "created_at");

        $this->addForeignKey(
            "fk-business_id-movement",
            "movement",
            "business_id",
            "business",
            "id",
            "CASCADE"
        );
        $this->addForeignKey(
            "fk-ingredient_id-movement",
            "movement",
            "ingredient_id",
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
        $this->dropForeignKey("fk-ingredient_id-movement", "movement");
        $this->dropForeignKey("fk-business_id-movement", "movement");
        $this->dropTable('{{%movement}}');
    }
}
