<?php

use yii\db\Migration;

class m171204_073324_update_scheme extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%person_month_profit}}', 'company_id',
            $this->integer(11)->notNull()->defaultValue(0)->unsigned()->comment('公司id')->after('id'));
        $this->addColumn('{{%month_performance_rank}}', 'company_id',
            $this->integer(11)->notNull()->defaultValue(0)->unsigned()->comment('公司id')->after('id'));
        $this->addColumn('{{%receipt}}', 'company_id',
            $this->integer(11)->notNull()->defaultValue(0)->unsigned()->comment('公司id')->after('id'));
    }

    public function safeDown()
    {
        echo "m171204_073324_update_scheme cannot be reverted.\n";

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
