<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%standard_recipe}}`.
 */
class m230401_092231_create_standard_recipe_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%standard_recipe}}', [
            'id' => $this->primaryKey(),
            'business_id' => $this->integer()->notNull(),
            'flowchart' => $this->text(),
            'equipment' => $this->text(),
            'steps' => $this->text(),
            'allergies' => $this->text(),
            'type' => $this->string()->notNull()
        ]);

        $this->createIndex(
            "idx-business_id-standard_recipe",
            'standard_recipe',
            'business_id'
        );

        $this->addForeignKey(
            "fk-business_id-standard_recipe",
            "standard_recipe",
            "business_id",
            "business",
            "id",
            "CASCADE"
        );

        $this->createTable("standard_recipe_sub_standard_recipe", [
            'id' => $this->primaryKey(),
            'standard_recipe_id' => $this->integer()->notNull(),
            'sub_standard_recipe_id' => $this->integer()->notNull(),
            'quantity' => $this->float(),
        ]);

        $this->createIndex(
            "idx-standard_recipe_id-standard_recipe_sub_standard_recipe",
            "standard_recipe_sub_standard_recipe",
            "sub_standard_recipe_id"
        );
        $this->createIndex(
            "idx-sub_standard_recipe_id-standard_recipe_sub_standard_recipe",
            "standard_recipe_sub_standard_recipe",
            "sub_standard_recipe_id"
        );

        $this->addForeignKey(
            "fk-standard_recipe-standard_recipe_sub_standard_recipe",
            "standard_recipe_sub_standard_recipe",
            "standard_recipe_id",
            "standard_recipe",
            "id",
            "CASCADE"
        );
        $this->addForeignKey(
            "fk-sub_standard_recipe-standard_recipe_sub_standard_recipe",
            "standard_recipe_sub_standard_recipe",
            "sub_standard_recipe_id",
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
        $this->dropForeignKey("idx-business_id-standard_recipe", 'standard_recipe');
        $this->dropIndex("fk-business_id-standard_recipe", 'standard_recipe');
        $this->dropTable('{{%standard_recipe}}');
        $this->dropForeignKey("fk-standard_recipe-standard_recipe_sub_standard_recipe", "standard_recipe_sub_standard_recipe");
        $this->dropForeignKey("fk-standard_recipe-standard_recipe_sub_standard_recipe", "standard_recipe_sub_standard_recipe");
        $this->dropIndex("idx-standard_recipe_id-standard_recipe_sub_standard_recipe", "standard_recipe_sub_standard_recipe");
        $this->dropIndex("idx-sub_standard_recipe_id-standard_recipe_sub_standard_recipe", "standard_recipe_sub_standard_recipe");
        $this->dropTable("standard_recipe_sub_standard_recipe");
    }
}
