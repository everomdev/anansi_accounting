<?php

use yii\db\Migration;

class m260116_040443_add_requisition_time_status_to_movement extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%movement}}', 'requisition_time_status', $this->string(20)->null()->after('is_extemporaneous'));
        
        // Actualizar registros existentes basados en is_extemporaneous
        $this->execute("
            UPDATE {{%movement}} 
            SET requisition_time_status = CASE 
                WHEN is_extemporaneous = 1 THEN 'extemporaneous'
                ELSE 'on_time'
            END
            WHERE type = 'requisition'
        ");
        
        $this->addCommentOnColumn('{{%movement}}', 'requisition_time_status', 
            'Estado de tiempo de la requisición: on_time, out_of_time, extemporaneous');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%movement}}', 'requisition_time_status');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260116_040443_add_requisition_time_status_to_movement cannot be reverted.\n";

        return false;
    }
    */
}
