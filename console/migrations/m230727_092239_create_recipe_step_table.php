<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%recipe_step}}`.
 */
class m230727_092239_create_recipe_step_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%recipe_step}}', [
            'id' => $this->primaryKey(),
            'recipe_id' => $this->integer()->notNull(),
            'number' => $this->integer()->notNull(),
            'activity' => $this->string()->notNull(),
            'time' => $this->time(),
            'indicator' => $this->string()
        ]);

        $this->createIndex('idx-recipe_id-recipe_step', 'recipe_step', 'recipe_id');
        $this->createIndex('idx-number-recipe_step', 'recipe_step', 'number');
        $this->addForeignKey(
            'fk-recipe_id-recipe_step',
            'recipe_step',
            'recipe_id',
            'standard_recipe',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey("fk-recipe_id-recipe_step", "recipe_step");
        $this->dropIndex('idx-recipe_id-recipe_step', 'recipe_step');
        $this->dropIndex('idx-number-recipe_step', 'recipe_step');
        $this->dropTable('{{%recipe_step}}');
    }
}
