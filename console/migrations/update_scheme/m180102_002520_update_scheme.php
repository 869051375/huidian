<?php

use yii\db\Migration;

class m180102_002520_update_scheme extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('{{%information}}', 'source',
            $this->string(30)->notNull()->defaultValue('')->comment('来源'));
    }

    public function safeDown()
    {
        echo "m180102_002520_update_scheme cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171225_062910_update_scheme cannot be reverted.\n";

        return false;
    }
    */
}
