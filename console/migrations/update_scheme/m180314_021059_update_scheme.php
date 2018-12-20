<?php

use yii\db\Migration;

class m180314_021059_update_scheme extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%order}}', 'is_satisfaction',
            $this->smallInteger(1)->notNull()->defaultValue(0)->unsigned()->comment('是否满意，1满意，2一般，3不满意，默认0未选择')->after('is_renewal'));
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
