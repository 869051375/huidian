<?php

use yii\db\Migration;

class m180201_043451_update_scheme extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('{{%business_subject}}', 'operating_period_end',
            $this->bigInteger(10)->notNull()->defaultValue(0)->unsigned()->comment('经营期限结束'));
    }

    public function safeDown()
    {
        echo "m180201_043451_update_scheme cannot be reverted.\n";

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
