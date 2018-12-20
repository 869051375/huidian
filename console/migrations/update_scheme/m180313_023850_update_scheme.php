<?php

use yii\db\Migration;

class m180313_023850_update_scheme extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('{{%crm_customer}}', 'name',
            $this->string(30)->notNull()->defaultValue('')->comment('姓名'));

        $this->alterColumn('{{%crm_opportunity}}', 'customer_name',
            $this->string(30)->notNull()->defaultValue('')->comment('客户名称'));

        $this->alterColumn('{{%user}}', 'name',
            $this->string(30)->notNull()->defaultValue('')->comment('姓名'));
    }

    public function safeDown()
    {
        echo "m180313_023850_update_scheme cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180313_023850_update_scheme cannot be reverted.\n";

        return false;
    }
    */
}
