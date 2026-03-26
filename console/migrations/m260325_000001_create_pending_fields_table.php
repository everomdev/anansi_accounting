<?php
use yii\db\Migration;

/**
 * Handles the creation of table `{{%pending_field}}`.
 * Esta tabla servirá para insumos, recetas y subrecetas.
 */
class m260325_000001_create_pending_fields_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%pending_field}}', [
            'id' => $this->primaryKey(),
            'model_type' => $this->string(50)->notNull(), // 'ingredient', 'recipe', 'subrecipe'
            'model_id' => $this->integer()->notNull(),
            'field' => $this->string(100)->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);
        $this->createIndex('idx-pending_field-model', '{{%pending_field}}', ['model_type', 'model_id']);
    }

    public function safeDown()
    {
        $this->dropTable('{{%pending_field}}');
    }
}
