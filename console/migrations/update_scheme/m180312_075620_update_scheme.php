<?php

use yii\db\Migration;

class m180312_075620_update_scheme extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%crm_opportunity}}', 'send_time',
            $this->integer(10)->unsigned()->notNull()->defaultValue(0)->comment('转出时间戳')->after('send_administrator_id'));
    }

    public function safeDown()
    {
        echo "m180312_075620_update_scheme cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180312_075620_update_scheme cannot be reverted.\n";

        return false;
    }
    */
}
