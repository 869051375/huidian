<?php

use yii\db\Migration;

class m180724_014554_update_scheme extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%receipt}}', 'pay_account' ,
            $this->string(50)->notNull()->defaultValue('')->comment('打款账户')->after('status'));

        $this->addColumn('{{%receipt}}', 'receipt_company' ,
            $this->string(25)->notNull()->defaultValue('')->comment('收款公司')->after('pay_account'));

        $this->addColumn('{{%receipt}}', 'financial_code' ,
            $this->string(6)->notNull()->defaultValue('')->comment('财务明细编号')->after('receipt_company'));

        $this->addColumn('{{%receipt}}', 'audit_note' ,
            $this->string(100)->notNull()->defaultValue('')->comment('审核备注')->after('financial_code'));

        $this->addColumn('{{%order}}', 'is_contract_show' ,
            $this->boolean()->notNull()->defaultValue(1)->comment('合同订单是否为显示：1是，0否')->after('is_installment'));

    }

    public function safeDown()
    {
        echo "m180724_014554_update_scheme cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180724_014554_update_scheme cannot be reverted.\n";

        return false;
    }
    */
}
