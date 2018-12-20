<?php

use yii\db\Migration;

class m180306_032624_update_scheme extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%administrator}}', 'is_dimission',
            $this->boolean()->notNull()->defaultValue(0)->comment('是否离职，0否，1是，默认0')->after('is_belong_company'));
    }

    public function safeDown()
    {
        echo "m180306_032624_update_scheme cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180209_022542_update_scheme cannot be reverted.\n";

        return false;
    }
    */
}
