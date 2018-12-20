<?php

use yii\db\Migration;

class m171219_095451_update_scheme extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%order_record}}', 'receipt_id',
            $this->integer(11)->notNull()->defaultValue(0)->unsigned()->comment('确认回款表id')->after('order_id'));
    }

    public function safeDown()
    {
        echo "m171219_095451_update_scheme cannot be reverted.\n";

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
