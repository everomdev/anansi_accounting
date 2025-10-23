<?php

use yii\db\Migration;

class m251023_021528_add_alamacen_all_bussines extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Obtener todos los business_id existentes
        $businessIds = $this->db->createCommand('SELECT id FROM business')->queryColumn();

        foreach ($businessIds as $businessId) {
            // Verificar si ya existe un centro de consumo "Almacén" para este business
            $exists = $this->db->createCommand('
                SELECT COUNT(*) FROM consumption_center 
                WHERE business_id = :business_id AND name = :name
            ', [
                ':business_id' => $businessId,
                ':name' => 'Almacén'
            ])->queryScalar();

            // Si no existe, insertarlo
            if ($exists == 0) {
                $this->insert('consumption_center', [
                    'name' => 'Almacén',
                    'business_id' => $businessId
                ]);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "Esta migración no se puede revertir automáticamente porque no se puede distinguir cuáles centros de consumo 'Almacén' fueron agregados por esta migración y cuáles ya existían.\n";
        echo "Si necesita revertir, deberá eliminar manualmente los centros de consumo 'Almacén' no deseados.\n";
        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251023_021528_add_alamacen_all_bussines cannot be reverted.\n";

        return false;
    }
    */
}
