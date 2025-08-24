<?php

use yii\db\Migration;

class m250824_144648_update_existing_units_as_standard extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Actualizar todas las unidades existentes para marcarlas como estándar (custom = 0)
        // Esto asegura que las unidades que ya existían antes de implementar esta funcionalidad
        // se consideren como unidades estándar del sistema
        $this->update('{{%unit_of_measurement}}', ['custom' => 0], ['custom' => null]);
        $this->update('{{%unit_of_measurement}}', ['custom' => 0], 'custom IS NULL');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // No hay necesidad de revertir esto ya que solo estamos marcando unidades existentes
        echo "m250824_144648_update_existing_units_as_standard cannot be reverted.\n";
        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250824_144648_update_existing_units_as_standard cannot be reverted.\n";

        return false;
    }
    */
}
