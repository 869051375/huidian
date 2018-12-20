<?php

use yii\db\Migration;

class m180711_013938_update_scheme extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%administrator}}', 'latter' ,
            $this->string(30)->notNull()->defaultValue('')->comment('姓名全拼')->after('name'));
    }

    public function safeDown()
    {
        echo "m180711_013938_update_scheme cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180711_013938_update_scheme cannot be reverted.\n";

        return false;
    }
    */
}
