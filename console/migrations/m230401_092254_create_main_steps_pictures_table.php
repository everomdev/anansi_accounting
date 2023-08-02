<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%main_steps_pictures}}`.
 */
class m230401_092254_create_main_steps_pictures_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%main_steps_pictures}}', [
            'id' => $this->primaryKey(),
            'standard_recipe_id' => $this->integer()->notNull(),
            'description' => $this->text()
        ]);

        $this->createIndex(
            "idx-standard_recipe_id-main_steps_pictures",
            "main_steps_pictures",
            "standard_recipe_id"
        );

        $this->addForeignKey(
            "fk-standard_recipe_id-main_steps_pictures",
            "main_steps_pictures",
            "standard_recipe_id",
            "standard_recipe",
            "id",
            "CASCADE"
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey("idx-standard_recipe_id-main_steps_pictures", "main_steps_pictures");
        $this->dropIndex("fk-standard_recipe_id-main_steps_pictures", "main_steps_pictures");
        $this->dropTable('{{%main_steps_pictures}}');
    }
}
