<?php

use yii\db\Migration;

class m171115_021842_update_scheme extends Migration
{
    public function safeUp()
    {
        $this->dropColumn('{{%administrator}}', 'product_category_ids');
    }

    public function safeDown()
    {
        echo "m171115_021842_update_scheme cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171115_021842_update_scheme cannot be reverted.\n";

        return false;
    }
    */
}
