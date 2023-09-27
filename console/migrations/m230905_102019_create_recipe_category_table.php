<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%recipe_category}}`.
 */
class m230905_102019_create_recipe_category_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%recipe_category}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'business_id' => $this->integer(),
            'type' => $this->string()
        ]);

        $this->createIndex('idx-business_id-recipe_category', 'recipe_category', 'business_id');
        $this->addForeignKey(
            'fk-business_id-recipe_category',
            'recipe_category',
            'business_id',
            'business',
            'id',
            'CASCADE'
        );

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey("fk-business_id-recipe_category", 'recipe_category');
        $this->dropIndex("idx-business_id-recipe_category", 'recipe_category');
        $this->dropTable('{{%recipe_category}}');
    }
}
