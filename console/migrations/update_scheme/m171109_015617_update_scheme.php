<?php

use yii\db\Migration;

class m171109_015617_update_scheme extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%product_category}}', 'is_show_list',
            $this->boolean()->notNull()->defaultValue(0)->comment('(0:不显示在列表页，1:显示在列表页，默认0)')->after('is_show_nav'));
    }

    public function safeDown()
    {
        echo "m171109_015617_update_scheme cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171026_005417_update_scheme cannot be reverted.\n";

        return false;
    }
    */
}
