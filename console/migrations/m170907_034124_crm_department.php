<?php

use yii\db\Migration;

class m170907_034124_crm_department extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }
        // CRM部门表
        $this->createTable('{{%crm_department}}', [
            'id' => $this->primaryKey()->unsigned()->comment('部门id'),
            'parent_id' => $this->integer(11)->notNull()->defaultValue(0)->comment('父级id'),
            'leader_id' => $this->integer(11)->notNull()->defaultValue(0)->comment('部门负责人id，administrator表id'),
            'assign_administrator_id' => $this->integer(11)->notNull()->defaultValue(0)->comment('商机分配指定人员，administrator表id'),
            'company_id' => $this->integer(11)->notNull()->defaultValue(0)->comment('所属公司id'),
            'level' => $this->smallInteger(2)->notNull()->defaultValue(1)->comment('部门级别，默认1(1级，2级，3级等)'),
            'name' => $this->string(10)->notNull()->defaultValue('')->comment('名称'),
            'code' => $this->string(20)->notNull()->defaultValue('')->comment('编号'),
            'path' => $this->string(32)->notNull()->defaultValue('')->comment('层级关系路径：顶级部门-下级部门-下级的下级部门，根据id区分(如1-3-7)'),
            'status' => $this->smallInteger(2)->notNull()->defaultValue(1)->comment('状态，默认1，0:禁用（删除）, 1:有效'),
            'reward_proportion_id'=>$this->integer(11)->notNull()->defaultValue(0)->unsigned()->comment('关联提成方案id'),
            'created_at' => $this->integer(10)->notNull()->defaultValue(0)->comment('创建时'),
            'updated_at' => $this->integer(10)->notNull()->defaultValue(0)->comment('修改时间'),
        ], $tableOptions);
    }

    public function safeDown()
    {
        echo "m170907_034124_crm_department cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m170907_034124_crm_department cannot be reverted.\n";

        return false;
    }
    */
}
