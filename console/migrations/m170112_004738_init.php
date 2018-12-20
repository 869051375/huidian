<?php

use yii\db\Migration;

class m170112_004738_init extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        // 后台管理员账号表
        $this->createTable('{{%administrator}}', [
            'id' => $this->primaryKey()->unsigned(),
            'username' => $this->string(11)->notNull()->unique(),
            'name' => $this->string(10)->notNull()->defaultValue(''),
            'latter' => $this->string(30)->notNull()->defaultValue('')->comment('姓名全拼'),
            'is_root' => $this->boolean()->notNull()->defaultValue(0)->comment('是否为超级管理员，不受角色权限限制'),
            'is_department_manager' => $this->boolean()->notNull()->defaultValue(0)->comment('是否为所在部门领导/助理'),
            'phone' => $this->string(11)->notNull()->unique()->defaultValue(''),
            'auth_key' => $this->string(32)->notNull(),
            'password_hash' => $this->string()->notNull(),
            'password_reset_token' => $this->string(),
            'email' => $this->string(32)->notNull()->unique(),
            'call_center' => $this->string(6)->notNull()->defaultValue('')->comment('呼叫中心工号'),
            'type' => $this->smallInteger()->notNull()->defaultValue(1)->comment('1:管理员，2:客服，3:嘟嘟妹，4:服务人员，5:业务人员'),
            'status' => $this->smallInteger()->notNull()->defaultValue(1)->comment('0:禁用，1:正常'),
            'is_belong_company' => $this->boolean()->notNull()->defaultValue(0)->comment('是否启用公司与部门，0否，1是，默认0'),
            'is_dimission' => $this->boolean()->notNull()->defaultValue(0)->comment('是否离职，0否，1是，默认0'),
            'company_id' => $this->integer(11)->notNull()->defaultValue(0)->comment('公司id'),
            'department_id' => $this->integer(11)->notNull()->defaultValue(0)->comment('部门id,只有一级分类才有所属部门id'),
            'title' => $this->string(6)->notNull()->defaultValue('')->comment('职位'),
            'image' => $this->string(64)->notNull()->defaultValue('')->comment('头像'),
            'created_at' => $this->integer()->unsigned()->notNull(),
            'updated_at' => $this->integer()->unsigned()->notNull(),
        ], $tableOptions);

        // 插入超级管理员账号
        $this->insert('{{%administrator}}', [
            'username' => 'admin',
            'name' => '管理员',
            'is_root' => 1,
            'phone' => '',
            'auth_key' => 'tUu1qHcde0diwUol3xeI-18MuHkkprQI',
            // admin
            'password_hash' => Yii::$app->security->generatePasswordHash('admin'),
            'password_reset_token' => null,
            'email' => 'admin@juejinqifu.com',
            //'product_category_ids' => '0',
            'type' => 1,
            'status' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        // 后台管理员角色
        $this->createTable('{{%administrator_role}}', [
            'id' => $this->primaryKey()->unsigned(),
            'name' => $this->string(10)->notNull()->defaultValue('')->unique(),
            'type' => $this->integer(11)->notNull()->defaultValue(0),
            'status' => $this->boolean()->notNull()->defaultValue(1)->unsigned()->comment('状态（默认1上线,0下线）'),
            'created_at' => $this->integer()->unsigned()->notNull(),
        ],$tableOptions);

        // 后台管理员操作日志表
        $this->createTable('{{%administrator_log}}', [
            'id' => $this->primaryKey()->unsigned(),
            'administrator_id' => $this->integer()->unsigned()->notNull(),
            'administrator_name' => $this->string(10)->notNull()->defaultValue('')->comment('操作人姓名'),
            'desc' => $this->string()->notNull(),
            'type' => $this->smallInteger()->notNull()->defaultValue(1)->comment('1:一般操作，2:登录成功，3:登录失败，4:主动退出'),
            'ip' => $this->string(15)->notNull()->defaultValue('')->comment('ip地址'),
            'total' => $this->integer(11)->notNull()->defaultValue(0)->comment('登录次数总计，当type为2时有效'),
            'sign' => $this->integer(11)->notNull()->defaultValue(0)->comment('是否警告标红（默认1标红，0不标红）'),
            'created_at' => $this->integer()->unsigned()->notNull(),
        ], $tableOptions);

        // 系统配置参数表
        $this->createTable('{{%property}}', [
            'key' => $this->string(32)->notNull(),
            'desc' => $this->string(32)->notNull()->defaultValue(''),
            'value' => $this->text()->comment('格式数据为php序列化格式'),
        ], $tableOptions);
        $this->addPrimaryKey('key', '{{%property}}', ['key']);
    }

    public function down()
    {
        echo "m170112_004738_init cannot be reverted.\n";

        return false;
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
