<?php

use yii\db\Migration;

class m171024_055617_update_scheme extends Migration
{
    public function safeUp()
    {
        $this->dropIndex('password_reset_token','{{%administrator}}');
        $this->alterColumn('{{%message_remind}}', 'sign',
            $this->string(190));
    }

    public function safeDown()
    {
        echo "m171024_055617_update_scheme cannot be reverted.\n";

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
