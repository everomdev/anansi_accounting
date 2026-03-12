<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%empleados}}`.
 */
class m260311_000001_create_empleados_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%empleados}}', [
            'id' => $this->primaryKey(),
            'business_id' => $this->integer()->notNull(),
            'nombre' => $this->string(100)->notNull(),
            'apellido' => $this->string(100)->notNull(),
            'puesto' => $this->string(100)->notNull(),
            'area' => $this->string(100)->notNull(),
            'fecha_ingreso' => $this->date()->notNull(),
            'fecha_salida' => $this->date()->null(),
            'estado' => "ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo'",
            'telefono' => $this->string(20)->null(),
            'email' => $this->string(100)->null(),
            'direccion' => $this->text()->null(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // Índices
        $this->createIndex(
            'idx-empleados-business_id',
            '{{%empleados}}',
            'business_id'
        );

        $this->createIndex(
            'idx-empleados-estado',
            '{{%empleados}}',
            'estado'
        );

        $this->createIndex(
            'idx-empleados-puesto',
            '{{%empleados}}',
            'puesto'
        );

        // Foreign key
        $this->addForeignKey(
            'fk-empleados-business_id',
            '{{%empleados}}',
            'business_id',
            '{{%business}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-empleados-business_id', '{{%empleados}}');
        $this->dropTable('{{%empleados}}');
    }
}
