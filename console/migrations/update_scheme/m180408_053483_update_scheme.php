<?php

use yii\db\Migration;

class m180408_053483_update_scheme extends Migration
{
    public function safeUp()
    {
        $this->createIndex('is_vest', '{{%order}}', 'is_vest');
        $this->createIndex('administrator_id', '{{%order_team}}', 'administrator_id');
    }

    public function safeDown()
    {
        echo "m180408_053483_update_scheme cannot be reverted.\n";

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
