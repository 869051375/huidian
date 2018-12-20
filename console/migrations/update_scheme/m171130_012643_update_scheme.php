<?php

use yii\db\Migration;

class m171130_012643_update_scheme extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%order}}', 'company_id',
            $this->integer(11)->notNull()->defaultValue(0)->comment('公司id')->after('sign'));
        $this->addColumn('{{%clerk}}', 'company_id',
            $this->integer(11)->notNull()->defaultValue(0)->comment('公司id')->after('administrator_id'));
    }

    public function safeDown()
    {
        echo "m171127_071418_update_scheme cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171127_071418_update_scheme cannot be reverted.\n";

        return false;
    }
    */
}
