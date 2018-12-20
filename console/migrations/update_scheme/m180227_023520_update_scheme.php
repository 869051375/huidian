<?php

use yii\db\Migration;

class m180227_023520_update_scheme extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%administrator_log}}', 'sign',
            $this->integer(11)->notNull()->defaultValue(0)->comment('是否警告标红（默认1标红，0不标红）')->after('total'));
    }

    public function safeDown()
    {
        echo "m180227_023520_update_scheme cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180209_022542_update_scheme cannot be reverted.\n";

        return false;
    }
    */
}
