<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%ingredient_stock}}`.
 */
class m230401_092205_create_ingredient_stock_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%ingredient_stock}}', [
            'id' => $this->primaryKey(),
            'ingredient' => $this->string()->notNull(),
            'business_id' => $this->integer()->notNull(),
            'quantity' => $this->double(),
            'um' => $this->string()->notNull(),
            'yield' => $this->float(),
            'portions_per_unit' => $this->float(),
            'portion_um' => $this->string(),
            'observations' => $this->text()
        ]);

        $this->createIndex(
            'idx-business_id-ingredient_stock',
            'ingredient_stock',
            'business_id',
        );

        $this->addForeignKey(
            'fk-business_id-ingredient_stock',
            'ingredient_stock',
            'business_id',
            'business',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey("fk-business_id-ingredient_stock", 'ingredient_stock');
        $this->dropIndex("idx-business_id-ingredient_stock", 'ingredient_stock');
        $this->dropTable('{{%ingredient_stock}}');
    }
}
