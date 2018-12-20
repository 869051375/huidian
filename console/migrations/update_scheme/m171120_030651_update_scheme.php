<?php

use yii\db\Migration;

class m171120_030651_update_scheme extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%product}}', 'is_inventory_limit',
            $this->boolean()->notNull()->defaultValue(0)->comment('是否库存限制商品，1是，0否，默认0')->after('service_cycle'));
        $this->addColumn('{{%product}}', 'inventory_qty',
            $this->integer(8)->notNull()->defaultValue(0)->comment('商品库存数量，默认0')->after('is_inventory_limit'));
    }

    public function safeDown()
    {
        echo "m171120_030651_update_scheme cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171120_030651_update_scheme cannot be reverted.\n";

        return false;
    }
    */
}
