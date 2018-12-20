<?php

use yii\db\Migration;

class m171114_021131_update_scheme extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%crm_opportunity_product}}', 'qty',
            $this->integer(11)->unsigned()->notNull()->defaultValue(0)->comment('数量')->after('price'));
    }

    public function safeDown()
    {
        echo "m171114_021131_update_scheme cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171114_021131_update_scheme cannot be reverted.\n";

        return false;
    }
    */
}
