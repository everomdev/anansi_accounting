<?php

use yii\db\Migration;

/**
 * Class m230920_075011_add_category_id_to_menu
 */
class m230920_075011_add_category_id_to_menu extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('menu', 'category_id', $this->integer());
        $this->createIndex('idx-category_id-menu', 'menu', 'category_id');
        $this->addForeignKey(
            'fk-category_id-menu',
            'menu',
            'category_id',
            'recipe_category',
            'id',
            'SET NULL'
        );

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-category_id-menu', 'menu');
        $this->dropIndex('idx-category_id-menu', 'menu');
        $this->dropColumn('category_id', 'menu');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230920_075011_add_category_id_to_menu cannot be reverted.\n";

        return false;
    }
    */
}
