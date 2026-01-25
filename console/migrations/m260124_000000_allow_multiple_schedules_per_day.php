<?php

use yii\db\Migration;

/**
 * Permite múltiples rangos de horarios para el mismo día en consumption_center_schedule
 * Elimina el índice UNIQUE de (consumption_center_id, day_of_week)
 */
class m260124_000000_allow_multiple_schedules_per_day extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Eliminar el índice UNIQUE existente
        $this->dropIndex(
            'idx-cc_schedule-center_day',
            '{{%consumption_center_schedule}}'
        );

        // Crear un índice normal (no único) para mantener el rendimiento de las consultas
        $this->createIndex(
            'idx-cc_schedule-center_day',
            '{{%consumption_center_schedule}}',
            ['consumption_center_id', 'day_of_week'],
            false // NO UNIQUE - permite múltiples registros
        );

        echo "✅ Ahora se permiten múltiples rangos de horarios para el mismo día.\n";
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Eliminar el índice normal
        $this->dropIndex(
            'idx-cc_schedule-center_day',
            '{{%consumption_center_schedule}}'
        );

        // Restaurar el índice UNIQUE (esto fallará si existen duplicados)
        $this->createIndex(
            'idx-cc_schedule-center_day',
            '{{%consumption_center_schedule}}',
            ['consumption_center_id', 'day_of_week'],
            true // UNIQUE
        );

        echo "⚠️ Restaurado índice UNIQUE. Solo un horario por día permitido.\n";
    }
}
