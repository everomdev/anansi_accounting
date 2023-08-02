<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%menu}}`.
 */
class m230401_092327_create_menu_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%menu}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'total_cost' => $this->float(),
            'total_price' => $this->float(),
            'cost_precent' => $this->float(),
            'business_id' => $this->integer()->notNull()
        ]);

        $this->createIndex(
            "idx-business_id-menu",
            'menu',
            'business_id',
        );

        $this->addForeignKey(
            "fk-business_id-menu",
            "menu",
            "business_id",
            "business",
            "id",
            "CASCADE",
            "CASCADE"
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey("idx-business_id-menu", "menu");
        $this->dropIndex("fk-business_id-menu", "menu");
        $this->dropTable('{{%menu}}');
    }
}
