<?php

use yii\db\Migration;

class m180227_053934_update_scheme extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%crm_opportunity}}', 'last_record_creator_id',
            $this->integer(10)->notNull()->defaultValue(0)->unsigned()->comment('最后跟进人id')->after('last_record'));

        $this->addColumn('{{%crm_opportunity}}', 'last_record_creator_name',
            $this->string(10)->notNull()->defaultValue('')->comment('最后跟进人姓名')->after('last_record_creator_id'));

        $this->addColumn('{{%crm_opportunity}}', 'is_protect',
            $this->boolean()->notNull()->defaultValue(0)->unsigned()->comment('商机是否受到保护，1是，0否，默认0')->after('next_follow_time'));

        $this->addColumn('{{%crm_opportunity}}', 'opportunity_public_id',
            $this->integer(10)->notNull()->defaultValue(0)->unsigned()->comment('商机公海id，0:不属于公海，否则属于公海，默认为0')->after('is_protect'));

        $this->addColumn('{{%crm_opportunity}}', 'extract_time',
            $this->integer(10)->notNull()->defaultValue(0)->unsigned()->comment('商机提取成功时间戳,默认0')->after('opportunity_public_id'));

        $this->addColumn('{{%crm_opportunity}}', 'move_public_time',
            $this->integer(10)->notNull()->defaultValue(0)->unsigned()->comment('移入商机公海时间戳,默认0')->after('extract_time'));

        $this->addColumn('{{%crm_customer}}', 'last_record_creator_id',
            $this->integer(10)->notNull()->defaultValue(0)->unsigned()->comment('最后一次跟进操作人id')->after('last_record'));

        $this->addColumn('{{%crm_customer}}', 'last_record_creator_name',
            $this->string(10)->notNull()->defaultValue('')->comment('最后一次跟进操作人姓名')->after('last_record_creator_id'));

        $this->addColumn('{{%crm_customer}}', 'operation_time',
            $this->integer(10)->notNull()->defaultValue(0)->unsigned()->comment('最后一次跟进时间和客户操作记录时间的最大值')->after('last_record_creator_name'));

        $this->addColumn('{{%crm_customer}}', 'last_operation_creator_id',
            $this->integer(10)->notNull()->defaultValue(0)->unsigned()->comment('最后一次跟进时间和客户操作记录时间的最大值时的操作人id')->after('operation_time'));

        $this->addColumn('{{%crm_customer}}', 'last_operation_creator_name',
            $this->string(10)->notNull()->defaultValue('')->comment('最后一次跟进时间和客户操作记录时间的最大值时的操作人姓名')->after('last_operation_creator_id'));

        $this->addColumn('{{%crm_customer}}', 'is_protect',
            $this->boolean()->notNull()->defaultValue(0)->unsigned()->comment('是否受到保护，1是，0否，默认0')->after('last_operation_creator_name'));

        $this->addColumn('{{%crm_customer}}', 'customer_public_id',
            $this->integer(10)->notNull()->defaultValue(0)->unsigned()->comment('客户公海id，0:不属于公海，否则属于公海，默认为0')->after('is_protect'));

        $this->addColumn('{{%crm_customer}}', 'extract_time',
            $this->integer(10)->notNull()->defaultValue(0)->unsigned()->comment('客户提取成功时间戳,默认0')->after('customer_public_id'));

        $this->addColumn('{{%crm_customer}}', 'move_public_time',
            $this->integer(10)->notNull()->defaultValue(0)->unsigned()->comment('移入客户公海时间戳,默认0')->after('extract_time'));
    }

    public function safeDown()
    {
        echo "m180227_053934_update_scheme cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180227_053934_update_scheme cannot be reverted.\n";

        return false;
    }
    */
}
