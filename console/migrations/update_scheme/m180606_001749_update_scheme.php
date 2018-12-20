<?php

use yii\db\Migration;

class m180606_001749_update_scheme extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%order_calculate_collect}}', 'knot_expected_amount' ,
            $this->decimal(15,2)->notNull()->defaultValue(0)->comment('结转预计利润金额')->after('correct_expected_amount'));
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
