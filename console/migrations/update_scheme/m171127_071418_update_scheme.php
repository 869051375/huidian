<?php

use yii\db\Migration;

class m171127_071418_update_scheme extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%crm_department}}', 'company_id',
            $this->integer(11)->notNull()->defaultValue(0)->unsigned()->comment('所属公司id')->after('assign_administrator_id'));

        $this->addColumn('{{%administrator}}', 'is_belong_company',
            $this->boolean()->notNull()->defaultValue(0)->comment('是否启用公司与部门，0否，1是，默认0')->after('status'));
        $this->addColumn('{{%administrator}}', 'company_id',
            $this->integer(11)->notNull()->defaultValue(0)->comment('公司id')->after('is_belong_company'));
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
