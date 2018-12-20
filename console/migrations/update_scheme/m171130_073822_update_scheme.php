<?php

use yii\db\Migration;

class m171130_073822_update_scheme extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%crm_customer}}', 'company_id',
            $this->integer(11)->notNull()->defaultValue(0)->unsigned()->comment('所属公司id')->after('is_receive'));

        $this->addColumn('{{%crm_opportunity}}', 'company_id',
            $this->integer(11)->notNull()->defaultValue(0)->unsigned()->comment('所属公司id')->after('is_receive'));

        $this->addColumn('{{%crm_customer_combine}}', 'company_id',
            $this->integer(11)->notNull()->defaultValue(0)->unsigned()->comment('所属公司id')->after('level'));

    }

    public function safeDown()
    {
        echo "m171130_073822_update_scheme cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171130_073822_update_scheme cannot be reverted.\n";

        return false;
    }
    */
}
