<?php

use yii\db\Migration;

class m180419_011224_update_scheme extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%administrator_role}}', 'status',
            $this->boolean()->notNull()->defaultValue(1)->unsigned()->comment('状态（默认1上线,0下线）')->after('type'));
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
