<?php

use yii\db\Migration;

/**
 * Class m230617_173333_add_category_id_to_ingredient_stock_table
 */
class m230617_173333_add_category_id_to_ingredient_stock_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('ingredient_stock', "category_id", $this->integer());
        $this->addColumn('ingredient', "category_id", $this->integer());

        $this->addForeignKey(
            "fk-category_id-ingredient",
            "ingredient",
            "category_id",
            "category",
            "id",
            "CASCADE"
        );

        $this->addForeignKey(
            "fk-category_id-ingredient_stock",
            "ingredient_stock",
            "category_id",
            "category",
            "id",
            "CASCADE"
        );


    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey("fk-category_id-ingredient_stock", "ingredient_stock");
        $this->dropForeignKey("fk-category_id-ingredient", "ingredient");

        $this->dropColumn('ingredient_stock', "category_id");
        $this->dropColumn('ingredient', "category_id");

    }
}
