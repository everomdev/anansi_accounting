<?php

use yii\db\Migration;

class m260224_171714_add_urgency_to_movement extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%movement}}', 'urgency', $this->string(20)->defaultValue('normal')->after('status'));
        
        // Añadir índice para mejorar búsquedas/ordenamiento
        $this->createIndex(
            'idx-movement-urgency',
            '{{%movement}}',
            'urgency'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-movement-urgency', '{{%movement}}');
        $this->dropColumn('{{%movement}}', 'urgency');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260224_171714_add_urgency_to_movement cannot be reverted.\n";

        return false;
    }
    */
}
