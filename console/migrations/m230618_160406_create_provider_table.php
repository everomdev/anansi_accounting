<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%provider}}`.
 */
class m230618_160406_create_provider_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%provider}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'address' => $this->string(),
            'phone' => $this->string(),
            'second_phone' => $this->string(),
            'email' => $this->string(),
            'fax' => $this->string(),
            'business_id' => $this->integer()->notNull()
        ]);

        $this->createIndex("idx-name-provider", "provider", "name");
        $this->createIndex("idx-address-provider", "provider", "address");
        $this->createIndex("idx-phone-provider", "provider", "phone");
        $this->createIndex("idx-second_phone-provider", "provider", "second_phone");
        $this->createIndex("idx-email-provider", "provider", "email");
        $this->createIndex("idx-fax-provider", "provider", "fax");
        $this->createIndex("idx-business_id-provider", "provider", "business_id");

        $this->addForeignKey(
            "fk-business_id-provider",
            "provider",
            "business_id",
            "business",
            "id",
            "CASCADE"
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey("fk-business_id-provider", "provider");
        $this->dropIndex("idx-name-provider", "provider");
        $this->dropIndex("idx-address-provider", "provider");
        $this->dropIndex("idx-phone-provider", "provider");
        $this->dropIndex("idx-second_phone-provider", "provider");
        $this->dropIndex("idx-email-provider", "provider");
        $this->dropIndex("idx-fax-provider", "provider");
        $this->dropIndex("idx-business_id-provider", "provider");
        $this->dropTable('{{%provider}}');
    }
}
