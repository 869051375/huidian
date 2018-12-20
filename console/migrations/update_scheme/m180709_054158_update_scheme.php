<?php

use yii\db\Migration;

class m180709_054158_update_scheme extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%crm_opportunity}}', 'contract_id' ,
            $this->integer(11)->notNull()->defaultValue(0)->comment('合同id')->after('virtual_order_id'));
    }

    public function safeDown()
    {
        echo "m180709_054158_update_scheme cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180709_054158_update_scheme cannot be reverted.\n";

        return false;
    }
    */
}
