<?php

use yii\db\Migration;

class m171221_031658_update_scheme extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('{{%business_subject}}', 'enterprise_type',
            $this->string(40)->notNull()->defaultValue('')->comment('企业类型'));
    }

    public function safeDown()
    {
        echo "m171221_031658_update_scheme cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171219_095451_update_scheme cannot be reverted.\n";

        return false;
    }
    */
}
