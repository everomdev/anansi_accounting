<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%requisition_audit}}`.
 */
class m260109_202011_create_requisition_audit_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%requisition_audit}}', [
            'id' => $this->primaryKey(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%requisition_audit}}');
    }
}
