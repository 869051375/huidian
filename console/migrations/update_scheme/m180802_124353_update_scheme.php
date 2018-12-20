<?php

use yii\db\Migration;

class m180802_124353_update_scheme extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('{{%product_category}}', 'content',
            $this->string(200)->defaultValue('')->comment('页面底部描述')->after('keywords'));
       
    }

    public function safeDown()
    {
        echo "m180802_124353_update_scheme cannot be reverted.\n";

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
