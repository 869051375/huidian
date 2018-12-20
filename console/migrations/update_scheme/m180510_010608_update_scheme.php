<?php

use yii\db\Migration;

class m180510_010608_update_scheme extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%expected_profit_settlement_detail}}', 'virtual_order_id',
            $this->integer(11)->notNull()->defaultValue(0)->unsigned()->comment('虚拟订单id')->after('order_id'));
        $this->addColumn('{{%expected_profit_settlement_detail}}', 'sn',
            $this->string(16)->notNull()->defaultValue('')->comment('子订单号')->after('virtual_order_id'));
        $this->addColumn('{{%expected_profit_settlement_detail}}', 'v_sn',
            $this->string(17)->notNull()->defaultValue('')->comment('虚拟订单号')->after('sn'));
        $this->addColumn('{{%expected_profit_settlement_detail}}', 'type',
            $this->integer(10)->notNull()->defaultValue(0)->unsigned()->comment('预计利润计算类型,默认0,（0:常规计算,1:更正计算,2:结转计算）')->after('v_sn'));
        $this->addColumn('{{%expected_profit_settlement_detail}}', 'company_id',
            $this->integer(10)->notNull()->defaultValue(0)->unsigned()->comment('公司id')->after('type'));
        $this->addColumn('{{%expected_profit_settlement_detail}}', 'company_name',
            $this->string(15)->notNull()->defaultValue('')->comment('公司名称')->after('company_id'));
        $this->addColumn('{{%expected_profit_settlement_detail}}', 'title',
            $this->string(10)->notNull()->defaultValue('')->comment('金额名称')->after('company_name'));
        $this->addColumn('{{%expected_profit_settlement_detail}}', 'remark',
            $this->string(30)->notNull()->defaultValue('')->comment('金额备注')->after('title'));
        $this->addColumn('{{%order_expected_cost}}', 'type',
            $this->integer(10)->notNull()->defaultValue(0)->unsigned()->comment('成本类型，默认0录入，1计算')->after('day'));
    }

    public function safeDown()
    {
        echo "m180510_010608_update_scheme cannot be reverted.\n";

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
