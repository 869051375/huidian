<?php

use yii\db\Migration;

class m180329_022414_update_scheme extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%customer_public}}', 'release_time',
            $this->integer(5)->notNull()->defaultValue(0)->unsigned()->comment('已提取的客户在多长时间内不能主动释放。必须要小于执行规则时间，如:120小时，默认:0')->after('move_time'));
    }

    public function safeDown()
    {
        echo "m180329_022414_update_scheme cannot be reverted.\n";

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
