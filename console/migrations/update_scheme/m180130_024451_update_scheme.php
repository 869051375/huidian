<?php

use yii\db\Migration;

class m180130_024451_update_scheme extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%customer_service}}', 'allot_number',
            $this->integer(11)->notNull()->defaultValue(0)->unsigned()->comment('马甲订单分配数')->after('servicing_number'));
    }

    public function safeDown()
    {
        echo "m180130_024451_update_scheme cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171219_095451_update_scheme cannot be reverted.\n";

        return false;
    }
    */
}
