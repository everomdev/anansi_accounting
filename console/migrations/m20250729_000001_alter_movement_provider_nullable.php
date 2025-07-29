<?php

use yii\db\Migration;

class m20250729_000001_alter_movement_provider_nullable extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('movement', 'provider', $this->string(255)->null());
    }

    public function safeDown()
    {
        $this->alterColumn('movement', 'provider', $this->string(255)->notNull());
    }
}
