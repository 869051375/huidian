<?php

use yii\db\Migration;

class m171201_021324_update_scheme extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%crm_customer_log}}', 'subject_id',
            $this->integer(11)->notNull()->defaultValue(0)->unsigned()->comment('业务主体id')->after('opportunity_id'));
    }

    public function safeDown()
    {
        echo "m171201_021324_update_scheme cannot be reverted.\n";

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
