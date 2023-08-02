<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%menu_standard_recipe}}`.
 */
class m230401_092336_create_menu_standard_recipe_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%menu_standard_recipe}}', [
            'id' => $this->primaryKey(),
            'standard_recipe_id' => $this->integer()->notNull(),
            'menu_id' => $this->integer()->notNull()
        ]);

        $this->createIndex(
            "idx-standard_recipe_id-menu_standard_recipe",
            "menu_standard_recipe",
            "standard_recipe_id"
        );

        $this->addForeignKey(
            "fk-standard_recipe_id-menu_standard_recipe",
            "menu_standard_recipe",
            "standard_recipe_id",
            "standard_recipe",
            "id",
            "CASCADE"
        );

        $this->createIndex(
            "idx-menu_id-menu_standard_recipe",
            "menu_standard_recipe",
            "menu_id"
        );

        $this->addForeignKey(
            "fk-menu_id-menu_standard_recipe",
            "menu_standard_recipe",
            "menu_id",
            "menu",
            "id",
            "CASCADE",
            "CASCADE"
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey("fk-standard_recipe_id-menu_standard_recipe", "menu_standard_recipe");
        $this->dropForeignKey("fk-menu_id-menu_standard_recipe", "menu_standard_recipe");
        $this->dropIndex("idx-standard_recipe_id-menu_standard_recipe", "menu_standard_recipe");
        $this->dropIndex("idx-menu_id-menu_standard_recipe", "menu_standard_recipe");
        $this->dropTable('{{%menu_standard_recipe}}');
    }
}
