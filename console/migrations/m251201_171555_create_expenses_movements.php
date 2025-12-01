<?php

use yii\db\Migration;

class m251201_171555_create_expenses_movements extends Migration
{
    /**
     * {@inheritdoc}
     */
        public function safeUp()
    {
        $this->createTable('{{%expense_movements}}', [
            'id' => $this->primaryKey(),
            'business_id' => $this->integer()->notNull(),
            'expense_id' => $this->integer()->notNull(),
            'type' => $this->string(50)->notNull()->comment('payment, adjustment'),
            'amount' => $this->decimal(10, 2)->notNull(),
            'payment_type' => $this->string(255)->null(),
            'invoice' => $this->string(255)->null(),
            'observations' => $this->text()->null(),
            'movement_date' => $this->date()->notNull(),
            'created_at' => $this->timestamp()->null()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->null()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // Crear índices
        $this->createIndex(
            'idx-expense_movements-business_id',
            '{{%expense_movements}}',
            'business_id'
        );

        $this->createIndex(
            'idx-expense_movements-expense_id',
            '{{%expense_movements}}',
            'expense_id'
        );

        $this->createIndex(
            'idx-expense_movements-movement_date',
            '{{%expense_movements}}',
            'movement_date'
        );

        // Crear llaves foráneas
        $this->addForeignKey(
            'fk-expense_movements-business_id',
            '{{%expense_movements}}',
            'business_id',
            '{{%business}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-expense_movements-expense_id',
            '{{%expense_movements}}',
            'expense_id',
            '{{%expenses}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Eliminar llaves foráneas
        $this->dropForeignKey('fk-expense_movements-expense_id', '{{%expense_movements}}');
        $this->dropForeignKey('fk-expense_movements-business_id', '{{%expense_movements}}');

        // Eliminar índices
        $this->dropIndex('idx-expense_movements-movement_date', '{{%expense_movements}}');
        $this->dropIndex('idx-expense_movements-expense_id', '{{%expense_movements}}');
        $this->dropIndex('idx-expense_movements-business_id', '{{%expense_movements}}');

        // Eliminar tabla
        $this->dropTable('{{%expense_movements}}');
    }
}
