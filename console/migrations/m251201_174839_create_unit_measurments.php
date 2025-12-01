<?php

use yii\db\Migration;

class m251201_174839_create_unit_measurments extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%expense_unit_measurements}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(100)->notNull(),
            'business_id' => $this->integer()->notNull(),
            'created_at' => $this->timestamp()->null()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->null()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // Crear índices
        $this->createIndex(
            'idx-expense_unit_measurements-business_id',
            '{{%expense_unit_measurements}}',
            'business_id'
        );

        $this->createIndex(
            'idx-expense_unit_measurements-unique',
            '{{%expense_unit_measurements}}',
            ['name', 'business_id'],
            true
        );

        // Crear llave foránea
        $this->addForeignKey(
            'fk-expense_unit_measurements-business_id',
            '{{%expense_unit_measurements}}',
            'business_id',
            '{{%business}}',
            'id',
            'CASCADE'
        );

        // Insertar unidades por defecto para cada negocio
        $this->insertDefaultUnits();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-expense_unit_measurements-business_id', '{{%expense_unit_measurements}}');
        $this->dropIndex('idx-expense_unit_measurements-unique', '{{%expense_unit_measurements}}');
        $this->dropIndex('idx-expense_unit_measurements-business_id', '{{%expense_unit_measurements}}');
        $this->dropTable('{{%expense_unit_measurements}}');
    }

    /**
     * Inserta las unidades de medida por defecto para todos los negocios
     */
    private function insertDefaultUnits()
    {
        $units = [
            'Litro', 'Mililitro', 'Galón', 'Bote', 'Botella', 'Bidón', 'Garrafa', 'Paquete', 'Caja', 'Pieza',
            'Unidad', 'Kilogramo', 'Rollo', 'Paca', 'kWh', 'm3', 'Recarga', 'Mes', 'Año', 'Bimestre', 'Trimestre', 
            'Semestral', 'Evento', 'Servicio', 'Contrato', 'Licencia', 'Suscripción', 'Proyecto', 'Set', 
            'Docena', 'Bolsa', 'Lote', 'Kit', 'Hora', 'Visita técnica', 'Reparación', 'Instalación', 
            'Viaje', 'Km', 'Campaña', 'Anuncio', 'Pauta', 'Crédito', 'Post', 'Contenido', 'Sesión', 
            'Dispositivo', 'Póliza'
        ];

        // Obtener todos los negocios
        $businesses = $this->db->createCommand('SELECT id FROM business')->queryAll();

        foreach ($businesses as $business) {
            $businessId = $business['id'];
            
            foreach ($units as $unit) {
                $this->insert('{{%expense_unit_measurements}}', [
                    'name' => $unit,
                    'business_id' => $businessId,
                ]);
            }
        }
    }
}
