<?php

use yii\db\Migration;

class m180209_022542_update_scheme extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%administrator_log}}', 'ip',
            $this->string(15)->notNull()->defaultValue('')->comment('ip地址')->after('type'));

        $this->addColumn('{{%administrator_log}}', 'total',
            $this->integer(11)->notNull()->defaultValue(0)->comment('登录次数总计，当type为2时有效')->after('ip'));

        $this->alterColumn('{{%administrator_log}}', 'type',
            $this->smallInteger()->notNull()->defaultValue(1)->comment('1:一般操作，2:登录成功，3:登录失败, 4:主动退出'));
    }

    public function safeDown()
    {
        echo "m180209_022542_update_scheme cannot be reverted.\n";

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
