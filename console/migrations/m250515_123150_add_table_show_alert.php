<?php

use yii\db\Migration;

class m250515_123150_add_table_show_alert extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%user_notification_status}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'last_backup_reminder' => $this->dateTime()->null(),
            'show_backup_reminder' => $this->tinyInteger(1)->defaultValue(0),
        ]);
        
        // Crear clave foránea
        $this->addForeignKey(
            'fk-user_notification_status-user_id',
            '{{%user_notification_status}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );
        
        // Inicializar todos los usuarios actuales
        $this->execute("
            INSERT INTO {{%user_notification_status}} (user_id, show_backup_reminder)
            SELECT id, 1 FROM {{%user}}
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-user_notification_status-user_id', '{{%user_notification_status}}');
        $this->dropTable('{{%user_notification_status}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250515_123150_add_table_show_alert cannot be reverted.\n";

        return false;
    }
    */
}
