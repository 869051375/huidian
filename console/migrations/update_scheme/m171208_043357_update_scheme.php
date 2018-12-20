<?php

use yii\db\Migration;

class m171208_043357_update_scheme extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%month_profit_record}}', 'is_settlement_performance',
            $this->smallInteger(4)->notNull()->defaultValue(0)->comment('是否结算业绩（默认0未结算，1准备结算中，2已结算）')->after('range_end_time'));
    }

    public function safeDown()
    {
        echo "m171201_021324_update_scheme cannot be reverted.\n";

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
