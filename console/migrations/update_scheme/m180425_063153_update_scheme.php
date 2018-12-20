<?php

use yii\db\Migration;

class m180425_063153_update_scheme extends Migration
{
    public function safeUp()
    {
        $this->createIndex('customer_public_company_id', '{{%crm_customer}}', ['customer_public_id','company_id']);
        $this->createIndex('customer_id_type', '{{%crm_customer_log}}', ['customer_id','type']);
        $this->createIndex('administrator_id','{{%crm_customer_combine}}','administrator_id');
    }

    public function safeDown()
    {
        echo "m180425_063153_update_scheme cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180425_063153_update_scheme cannot be reverted.\n";

        return false;
    }
    */
}
