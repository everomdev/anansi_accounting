<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%ingredient_standard_recipe}}`.
 */
class m230401_092311_create_ingredient_standard_recipe_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%ingredient_standard_recipe}}', [
            'id' => $this->primaryKey(),
            'standard_recipe_id' => $this->integer()->notNull(),
            'ingredient_id' => $this->integer()->notNull(),
            'quantity' => $this->float(),
        ]);

        $this->createIndex(
            "idx-standard_recipe_id-ingredient_standard_recipe",
            "ingredient_standard_recipe",
            "standard_recipe_id"
        );

        $this->addForeignKey(
            "fk-standard_recipe_id-ingredient_standard_recipe",
            "ingredient_standard_recipe",
            "standard_recipe_id",
            "standard_recipe",
            "id",
            "CASCADE"
        );

        $this->createIndex(
            'idx-ingredient_id-ingredient_standard_recipe',
            'ingredient_standard_recipe',
            'ingredient_id',
        );
        $this->addForeignKey(
            'fk-ingredient_id-ingredient_standard_recipe',
            'ingredient_standard_recipe',
            'ingredient_id',
            'ingredient',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey("fk-standard_recipe_id-ingredient_standard_recipe", "ingredient_standard_recipe");
        $this->dropForeignKey("fk-ingredient_id-ingredient_standard_recipe", "ingredient_standard_recipe");
        $this->dropForeignKey("idx-standard_recipe_id-ingredient_standard_recipe", "ingredient_standard_recipe");
        $this->dropForeignKey("idx-ingredient_id-ingredient_standard_recipe", "ingredient_standard_recipe");
        $this->dropTable('{{%ingredient_standard_recipe}}');
    }
}
