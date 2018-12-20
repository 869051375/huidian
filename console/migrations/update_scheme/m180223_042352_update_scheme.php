<?php

use yii\db\Migration;

class m180223_042352_update_scheme extends Migration
{
    public function safeUp()
    {
        $this->createIndex('customer_id', '{{%user}}', 'customer_id');
        $this->createIndex('opportunity_id', '{{%crm_opportunity_product}}', 'opportunity_id');
    }

    public function safeDown()
    {
        echo "m180223_042352_update_scheme cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180209_022542_update_scheme cannot be reverted.\n";

        return false;
    }
    */
}
