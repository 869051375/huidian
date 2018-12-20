<?php

use yii\db\Migration;

class m171120_025012_update_scheme extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%crm_customer_log}}', 'type',
            $this->integer(11)->notNull()->defaultValue(0)->unsigned()->comment('默认0跟进记录（1自然人，2企业主体，3客户操作记录，4商机操作记录）')->after('opportunity_id'));
        $this->addColumn('{{%crm_customer}}', 'birthday',
            $this->string(10)->notNull()->defaultValue('')->comment('客户生日（1949-10-01）')->after('gender'));
        $this->addColumn('{{%business_subject}}', 'updated_at',
            $this->integer(11)->notNull()->defaultValue(0)->unsigned()->comment('最后修改时间')->after('created_at'));
        $this->addColumn('{{%business_subject}}', 'creator_id',
            $this->integer(11)->notNull()->defaultValue(0)->unsigned()->comment('创建人id')->after('updated_at'));
        $this->addColumn('{{%business_subject}}', 'creator_name',
            $this->string(10)->notNull()->defaultValue('')->comment('创建人姓名')->after('creator_id'));
        $this->addColumn('{{%business_subject}}', 'updater_id',
            $this->integer(11)->notNull()->defaultValue(0)->unsigned()->comment('最后修改人id')->after('creator_name'));
        $this->addColumn('{{%business_subject}}', 'updater_name',
            $this->string(10)->notNull()->defaultValue('')->comment('最后修改人姓名')->after('updater_id'));
    }

    public function safeDown()
    {
        echo "m171120_025012_update_scheme cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171026_005417_update_scheme cannot be reverted.\n";

        return false;
    }
    */
}
