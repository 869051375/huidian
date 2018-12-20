<?php

use yii\db\Migration;

class m171024_055616_update_scheme extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%product_category}}', 'content',
            $this->text()->comment('页面底部描述')->after('keywords'));
       
    }

    public function safeDown()
    {
        echo "m171024_055616_update_scheme cannot be reverted.\n";

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
