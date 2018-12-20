<?php

use yii\db\Migration;

class m171123_020351_update_scheme extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%order}}', 'sign',
            $this->boolean()->notNull()->defaultValue(0)->comment('用于记录该订单是否在结算后修改业务员（默认0否，1是）')->after('settlement_month'));
        $this->addColumn('{{%expected_profit_settlement_detail}}', 'creator_name',
            $this->string(10)->notNull()->defaultValue('')->comment('创建人姓名')->after('created_at'));
        $this->addColumn('{{%expected_profit_settlement_detail}}', 'creator_id',
            $this->integer(11)->notNull()->defaultValue(0)->unsigned()->comment('创建人id')->after('creator_name'));
    }

    public function safeDown()
    {
        echo "m171123_020351_update_scheme cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171120_030651_update_scheme cannot be reverted.\n";

        return false;
    }
    */
}
