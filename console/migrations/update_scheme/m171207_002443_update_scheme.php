<?php

use yii\db\Migration;

class m171207_002443_update_scheme extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%customer_service}}', 'company_id',
            $this->integer(11)->notNull()->defaultValue(0)->comment('公司id')->after('id'));
        $this->addColumn('{{%salesman}}', 'company_id',
            $this->integer(11)->notNull()->defaultValue(0)->comment('公司id')->after('id'));
    }

    public function safeDown()
    {
        echo "m171207_002443_update_scheme cannot be reverted.\n";

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
