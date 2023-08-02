<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%category}}`.
 */
class m230617_164752_create_category_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%category}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'builtin' => $this->boolean()->defaultValue(false),
            'business_id' => $this->integer(),
        ]);

        $this->createIndex("idx-name-category", "category", "name");
        $this->createIndex("idx-business_id-category", "category", "business_id");
        $this->addForeignKey("fk-business_id-category", "category", "business_id", "business", "id", "CASCADE");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey("fk-business_id-category", "category");
        $this->dropIndex("idx-business_id-category", "category");
        $this->dropIndex("idx-name-category", "category");
        $this->dropTable('{{%category}}');
    }
}
