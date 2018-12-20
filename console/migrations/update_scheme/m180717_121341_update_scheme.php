<?php

use yii\db\Migration;

class m180717_121341_update_scheme extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%order}}', 'expected_profit_calculate' ,
            $this->boolean()->notNull()->defaultValue(0)->comment('是否计算业绩(0:否，1是，默认0)')->after('financial_code'));
    }

    public function safeDown()
    {
        echo "m180717_121341_update_scheme cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180717_121341_update_scheme cannot be reverted.\n";

        return false;
    }
    */
}
