<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%consumption_center_schedule}}`.
 * Tabla para guardar los horarios específicos por día de la semana para cada centro de consumo
 */
class m260118_000000_create_consumption_center_schedule_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%consumption_center_schedule}}', [
            'id' => $this->primaryKey(),
            'consumption_center_id' => $this->integer()->notNull()->comment('Centro de consumo'),
            'day_of_week' => $this->tinyInteger()->notNull()->comment('Día de la semana (0=Domingo, 1=Lunes, ..., 6=Sábado)'),
            'start_time' => $this->string(5)->notNull()->comment('Hora de inicio (HH:MM formato 24h)'),
            'end_time' => $this->string(5)->notNull()->comment('Hora de fin (HH:MM formato 24h)'),
            'is_active' => $this->boolean()->defaultValue(true)->comment('Si el horario está activo'),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // Índice único para evitar duplicados de día por centro
        $this->createIndex(
            'idx-cc_schedule-center_day',
            '{{%consumption_center_schedule}}',
            ['consumption_center_id', 'day_of_week'],
            true // UNIQUE - solo un horario por día por centro
        );

        // Índice para consultas rápidas por centro
        $this->createIndex(
            'idx-cc_schedule-consumption_center_id',
            '{{%consumption_center_schedule}}',
            'consumption_center_id'
        );

        // Índice para consultas por día
        $this->createIndex(
            'idx-cc_schedule-day_of_week',
            '{{%consumption_center_schedule}}',
            'day_of_week'
        );

        // Foreign key
        $this->addForeignKey(
            'fk-cc_schedule-consumption_center_id',
            '{{%consumption_center_schedule}}',
            'consumption_center_id',
            '{{%consumption_center}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-cc_schedule-consumption_center_id', '{{%consumption_center_schedule}}');
        $this->dropIndex('idx-cc_schedule-day_of_week', '{{%consumption_center_schedule}}');
        $this->dropIndex('idx-cc_schedule-consumption_center_id', '{{%consumption_center_schedule}}');
        $this->dropIndex('idx-cc_schedule-center_day', '{{%consumption_center_schedule}}');
        $this->dropTable('{{%consumption_center_schedule}}');
    }
}
