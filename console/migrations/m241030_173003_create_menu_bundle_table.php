<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%menu_bundle}}`.
 */
class m241030_173003_create_menu_bundle_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%menu_bundle}}', [
            'id' => $this->primaryKey(),
            'date' => $this->date(),
            'business_id' => $this->integer()->notNull(),
        ]);

        $this->createTable('menu_bundle_product', [
            'id' => $this->primaryKey(),
            'bundle_id' => $this->integer(),
            'entity_id' => $this->integer(),
            'entity_type' => $this->string()
        ]);

        $this->createIndex('idx-bundle_id-menu_bundle_product', 'menu_bundle_product', 'bundle_id');
        $this->createIndex('idx-business_id-menu_bundle', 'menu_bundle', 'business_Id');
        $this->addForeignKey('fk-business_id-menu_bundle', 'menu_bundle', 'business_id', 'business', 'id', 'CASCADE');
        $this->addForeignKey('fk-bundle_id-menu_bundle_product', 'menu_bundle_product', 'bundle_id', 'menu_bundle', 'id', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-business_id-menu_bundle', 'menu_bundle');
        $this->dropForeignKey('fk-bundle_id-menu_bundle_product', 'menu_bundle_product');
        $this->dropIndex('idx-business_id-menu_bundle', 'menu_bundle');
        $this->dropIndex('idx-bundle_id-menu_bundle_product', 'menu_bundle_product');
        $this->dropTable('{{%menu_bundle}}');
        $this->dropTable('{{%menu_bundle_product}}');


    }
}
