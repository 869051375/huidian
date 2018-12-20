<?php

use yii\db\Migration;

class m171024_055629_update_scheme extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%order}}', 'total_cost',
            $this->decimal(15,2)->notNull()->defaultValue(0)->comment('预计总成本，保留小数点后2位')->after('is_installment'));
        $this->addColumn('{{%order}}', 'real_cost',
            $this->decimal(15,2)->notNull()->defaultValue(0)->comment('当前实际成本，保留小数点后2位')->after('total_cost'));
        $this->addColumn('{{%order}}', 'first_payment_time',
            $this->integer(10)->notNull()->defaultValue(0)->unsigned()->comment('首次付款时间戳')->after('real_cost'));
        $this->addColumn('{{%order}}', 'settlement_month',
            $this->integer(6)->notNull()->defaultValue(0)->unsigned()->comment('预计利润结算月份')->after('first_payment_time'));
        $this->addColumn('{{%crm_department}}', 'reward_proportion_id',
            $this->integer(11)->notNull()->defaultValue(0)->unsigned()->comment('关联提成方案id')->after('status'));
    }

    public function safeDown()
    {
        echo "m171024_055629_update_scheme cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171024_055629_update_scheme cannot be reverted.\n";

        return false;
    }
    */
}
