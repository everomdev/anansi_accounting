<?php

use yii\db\Migration;

/**
 * Class m240915_202615_add_new_um_to_businesses
 */
class m240915_202615_add_new_um_to_businesses extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $businesses = (new \yii\db\Query())
            ->select('id')
            ->from('business')
            ->all();

        foreach ($businesses as $business) {
            $this->insert('unit_of_measurement', [
                'business_id' => $business['id'],
                'name' => 'Lata',
            ]);
            $this->insert('unit_of_measurement', [
                'business_id' => $business['id'],
                'name' => 'Bote',
            ]);


        }

        // change Ración to Porción
        $this->update('unit_of_measurement', ['name' => 'Porción'], ['name' => 'Ración']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240915_202615_add_new_um_to_businesses cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240915_202615_add_new_um_to_businesses cannot be reverted.\n";

        return false;
    }
    */
}
