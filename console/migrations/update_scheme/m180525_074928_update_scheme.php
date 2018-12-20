<?php

use yii\db\Migration;

class m180525_074928_update_scheme extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%administrator}}', 'call_center',
            $this->string(6)->notNull()->defaultValue('')->comment('呼叫中心工号')->after('email'));z
    }

    public function safeDown()
    {
        echo "m180525_074928_update_scheme cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180525_074928_update_scheme cannot be reverted.\n";

        return false;
    }
    */
}
