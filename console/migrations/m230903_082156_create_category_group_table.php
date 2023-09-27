<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%category_group}}`.
 */
class m230903_082156_create_category_group_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%category_group}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'color' => $this->string()->defaultValue('#ffffff')
        ]);
        $this->addColumn('category', 'group_id', $this->integer());
        $this->createIndex('idx-group_id-category', 'category', 'group_id');
        $this->addForeignKey('fk-group_id-category', 'category', 'group_id', 'category_group', 'id', 'CASCADE');

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-group_id-category', 'category');
        $this->dropIndex('idx-group_id-category', 'category');
        $this->dropColumn('category', 'group_id');
        $this->dropTable('{{%category_group}}');
    }
}
