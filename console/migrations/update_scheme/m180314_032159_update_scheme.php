<?php

use yii\db\Migration;

class m180314_032159_update_scheme extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%order}}', 'is_apply',
            $this->boolean()->notNull()->defaultValue(0)->unsigned()->comment('是否申请计算业绩,1申请，默认0不申请')->after('renewal_order_id'));
    }

    public function safeDown()
    {
        echo "m180314_032159_update_scheme cannot be reverted.\n";

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
