<?php

use yii\db\Migration;

class m180604_003306_update_scheme extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%month_profit_record}}', 'performance_start_time',
            $this->integer(10)->notNull()->defaultValue(0)->unsigned()->comment('业绩月结开始时间')->after('range_end_time'));
        $this->addColumn('{{%month_profit_record}}', 'performance_end_time',
            $this->integer(10)->notNull()->defaultValue(0)->unsigned()->comment('业绩月结结束时间')->after('performance_start_time'));
        $this->addColumn('{{%order_performance_collect}}', 'total_performance_amount' ,
            $this->decimal(15,2)->notNull()->defaultValue(0)->comment('订单业绩提成总收入')->after('fix_point_amount'));
    }

    public function safeDown()
    {
        echo "m180522_002406_update_scheme cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180329_022414_update_scheme cannot be reverted.\n";

        return false;
    }
    */
}
