<?php

use yii\db\Migration;

class m180503_070708_update_scheme extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%order_expected_cost}}', 'virtual_order_id',
            $this->integer(10)->notNull()->defaultValue(0)->unsigned()->comment('虚拟订单id')->after('order_id'));
        $this->addColumn('{{%order_expected_cost}}', 'cost_name',
            $this->string(15)->notNull()->defaultValue('')->comment('成本名称')->after('virtual_order_id'));
        $this->addColumn('{{%order_expected_cost}}', 'cost_price',
            $this->decimal(15,2)->notNull()->defaultValue(0)->comment('成本金额，保留小数点后2位')->after('cost_name'));
        $this->addColumn('{{%order_expected_cost}}', 'remark',
            $this->text()->comment('备注')->after('cost_price'));
        $this->addColumn('{{%order_expected_cost}}', 'year',
            $this->smallInteger(4)->notNull()->defaultValue(0)->unsigned()->comment('年')->after('remark'));
        $this->addColumn('{{%order_expected_cost}}', 'month',
            $this->smallInteger(2)->notNull()->defaultValue(0)->unsigned()->comment('月')->after('year'));
        $this->addColumn('{{%order_expected_cost}}', 'day',
            $this->smallInteger(2)->notNull()->defaultValue(0)->unsigned()->comment('日')->after('month'));
    }

    public function safeDown()
    {
        echo "m180503_070708_update_scheme cannot be reverted.\n";

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
