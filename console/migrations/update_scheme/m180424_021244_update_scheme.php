<?php

use yii\db\Migration;

class m180424_021244_update_scheme extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%receipt}}', 'is_separate_money',
            $this->boolean()->notNull()->defaultValue(0)->comment('是否分配回款默认0，0:不分配，1:分配')->after('receipt_date'));
        $this->addColumn('{{%order}}', 'payment_amount',
            $this->decimal(15,2)->notNull()->defaultValue(0)->comment('已付金额')->after('price'));
    }

    public function safeDown()
    {
        echo "m180424_021244_update_scheme cannot be reverted.\n";

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
