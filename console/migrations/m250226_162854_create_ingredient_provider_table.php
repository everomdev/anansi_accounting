<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%ingredient_provider}}`.
 */
class m250226_162854_create_ingredient_provider_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('ingredient_provider', [
            'ingredient_id' => $this->integer()->notNull(),
            'provider_id' => $this->integer()->notNull(),
        ]);

        $this->addPrimaryKey('pk-ingredient_provider', 'ingredient_provider', ['ingredient_id', 'provider_id']);

        $this->addForeignKey('fk-ingredient_provider-ingredient_id', 'ingredient_provider', 'ingredient_id', 'ingredient_stock', 'id', 'CASCADE');
        $this->addForeignKey('fk-ingredient_provider-provider_id', 'ingredient_provider', 'provider_id', 'provider', 'id', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-ingredient_provider-ingredient_id', 'ingredient_provider');
        $this->dropForeignKey('fk-ingredient_provider-provider_id', 'ingredient_provider');
        $this->dropTable('ingredient_provider');
    }
}
