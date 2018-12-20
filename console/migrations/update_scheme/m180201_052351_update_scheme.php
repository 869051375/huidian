<?php

use yii\db\Migration;

class m180201_052351_update_scheme extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('{{%crm_opportunity_record}}', 'department_name',
            $this->string(20)->notNull()->defaultValue('')->comment('所属部门名称'));
        $this->alterColumn('{{%performance_statistics}}', 'department_name',
            $this->string(20)->notNull()->defaultValue('')->comment('所属部门名称'));
        $this->alterColumn('{{%person_month_profit}}', 'top_department_name',
            $this->string(20)->notNull()->defaultValue('')->comment('一级部门名称'));
        $this->alterColumn('{{%person_month_profit}}', 'department_name',
            $this->string(20)->notNull()->defaultValue('')->comment('所属部门名称'));
        $this->alterColumn('{{%month_performance_rank}}', 'top_department_name',
            $this->string(20)->notNull()->defaultValue('')->comment('一级部门名称'));
        $this->alterColumn('{{%month_performance_rank}}', 'department_name',
            $this->string(20)->notNull()->defaultValue('')->comment('所属部门名称'));
        $this->alterColumn('{{%order_team}}', 'department_name',
            $this->string(20)->notNull()->defaultValue('')->comment('所属部门名称'));
        $this->alterColumn('{{%expected_profit_settlement_detail}}', 'department_name',
            $this->string(20)->notNull()->defaultValue('')->comment('所属部门名称'));
    }

    public function safeDown()
    {
        echo "m180201_052351_update_scheme cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171219_095451_update_scheme cannot be reverted.\n";

        return false;
    }
    */
}
