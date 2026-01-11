<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user_consumption_center}}`.
 */
class m260109_200347_create_user_consumption_center_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%user_consumption_center}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'consumption_center_id' => $this->integer()->notNull(),
            'is_default' => $this->boolean()->defaultValue(false),
            'created_at' => $this->integer()->notNull(),
        ]);

        // Índices
        $this->createIndex(
            'idx-user_consumption_center-user_id',
            '{{%user_consumption_center}}',
            'user_id'
        );
        
        $this->createIndex(
            'idx-user_consumption_center-consumption_center_id',
            '{{%user_consumption_center}}',
            'consumption_center_id'
        );
        
        // Índice único para evitar duplicados
        $this->createIndex(
            'idx-user_consumption_center-unique',
            '{{%user_consumption_center}}',
            ['user_id', 'consumption_center_id'],
            true
        );

        // Foreign keys
        $this->addForeignKey(
            'fk-user_consumption_center-user_id',
            '{{%user_consumption_center}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );
        
        $this->addForeignKey(
            'fk-user_consumption_center-consumption_center_id',
            '{{%user_consumption_center}}',
            'consumption_center_id',
            '{{%consumption_center}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-user_consumption_center-consumption_center_id', '{{%user_consumption_center}}');
        $this->dropForeignKey('fk-user_consumption_center-user_id', '{{%user_consumption_center}}');
        $this->dropIndex('idx-user_consumption_center-unique', '{{%user_consumption_center}}');
        $this->dropIndex('idx-user_consumption_center-consumption_center_id', '{{%user_consumption_center}}');
        $this->dropIndex('idx-user_consumption_center-user_id', '{{%user_consumption_center}}');
        $this->dropTable('{{%user_consumption_center}}');
    }
}
