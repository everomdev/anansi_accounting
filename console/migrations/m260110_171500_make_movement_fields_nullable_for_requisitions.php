<?php

use yii\db\Migration;

/**
 * Hacer nullable campos de movement que no aplican para requisiciones
 */
class m260110_171500_make_movement_fields_nullable_for_requisitions extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Las requisiciones no tienen estos campos, así que los hacemos nullable
        $this->alterColumn('{{%movement}}', 'provider', $this->string()->null());
        $this->alterColumn('{{%movement}}', 'quantity', $this->float()->null());
        $this->alterColumn('{{%movement}}', 'um', $this->string()->null());
        $this->alterColumn('{{%movement}}', 'ingredient_id', $this->integer()->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Revertir los cambios
        $this->alterColumn('{{%movement}}', 'provider', $this->string()->notNull());
        $this->alterColumn('{{%movement}}', 'quantity', $this->float()->notNull());
        $this->alterColumn('{{%movement}}', 'um', $this->string()->notNull());
        $this->alterColumn('{{%movement}}', 'ingredient_id', $this->integer()->notNull());
    }
}
