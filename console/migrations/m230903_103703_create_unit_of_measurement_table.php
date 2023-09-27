<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%unit_of_measurement}}`.
 */
class m230903_103703_create_unit_of_measurement_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%unit_of_measurement}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'business_id' => $this->integer(),
        ]);

        $this->createIndex('idx-business_id-unit_of_measurement', 'unit_of_measurement', 'business_id');
        $this->addForeignKey('fk-business_id-unit_of_measurement', 'unit_of_measurement', 'business_id', 'business', 'id', 'CASCADE');

        $businesses = (new \yii\db\Query())
            ->select('id')
            ->from('business')
            ->all();

        array_walk($businesses, function ($business) {
            $data = [
                ["Onza", $business['id']],
                ["Libra", $business['id']],
                ["Gramo", $business['id']],
                ["Taza", $business['id']],
                ["Cucharadita", $business['id']],
                ["Cucharada", $business['id']],
                ["Litro", $business['id']],
                ["Mililitro", $business['id']],
                ["Kilogramo", $business['id']],
                ["Pizca", $business['id']],
                ["Ración", $business['id']],
                ["Botella", $business['id']],
                ["Pieza", $business['id']],
                ["Rebanada", $business['id']],
                ["Gota", $business['id']],
                ["Paquete", $business['id']],
            ];
            Yii::$app->db->createCommand()
                ->batchInsert('unit_of_measurement', ['name', 'business_id'], $data)
                ->execute();

        }, $businesses);


    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-business_id-unit_of_measurement', 'unit_of_measurement');
        $this->dropIndex('idx-business_id-unit_of_measurement', 'unit_of_measurement');
        $this->dropTable('{{%unit_of_measurement}}');
    }
}
