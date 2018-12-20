<?php

use yii\db\Migration;

class m171225_062910_update_scheme extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('{{%crm_department}}', 'name',
            $this->string(20)->notNull()->defaultValue('')->comment('名称'));
    }

    public function safeDown()
    {
        echo "m171225_062910_update_scheme cannot be reverted.\n";

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
