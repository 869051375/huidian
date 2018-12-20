<?php

use yii\db\Migration;

class m170221_024411_user extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        // 注册用户数据表
        $this->createTable('{{%user}}', [
            'id' => $this->primaryKey()->unsigned()->comment('用户id'),
            'username' => $this->string(20)->notNull()->defaultValue('')->comment('用户名'),
            'name' => $this->string(30)->notNull()->defaultValue('')->comment('姓名'),
            'phone' => $this->string(11)->notNull()->defaultValue('')->comment('手机号'),
            'password_hash' => $this->string()->notNull()->defaultValue('')->comment('密码hash'),
            'email' => $this->string(64)->notNull()->defaultValue('')->comment('邮箱'),
            'address' => $this->string()->notNull()->defaultValue('')->comment('邮寄地址'),
            'last_login' => $this->integer(10)->notNull()->defaultValue(0)->comment('最后登录时间戳'),
            'auth_key' => $this->string(128)->notNull()->defaultValue(0)->comment('备用'),
            'password_reset_token' => $this->string(128)->defaultValue('')->comment('重置密码的令牌'),
            'wechat_open_id' => $this->string(128)->comment('微信OpenID'),
            'avatar' => $this->string(128)->notNull()->defaultValue('')->comment('客户头像'),
            'supervisor_id' => $this->integer(11)->notNull()->defaultValue(0)->unsigned()->comment('嘟嘟妹id'),
            'customer_service_id' => $this->integer(11)->notNull()->defaultValue(0)->unsigned()->comment('客服id'),
            'is_vest' => $this->boolean()->notNull()->defaultValue(0)->comment('是否为马甲：1是，0否'),
            'register_mode' => $this->boolean()->defaultValue(0)->comment('注册方式（0自主注册，1后台新增）'),
            'source_id' => $this->integer(11)->notNull()->defaultValue(0)->unsigned()->comment('用户来源id'),
            'source_name' => $this->string(20)->notNull()->defaultValue('')->comment('用户来源'),
            'customer_id' => $this->integer(10)->notNull()->defaultValue(0)->unsigned()->comment('客户id'),
            'creator_id' => $this->integer(11)->notNull()->defaultValue(0)->unsigned()->comment('管理员id'),
            'creator_name' => $this->string(10)->notNull()->defaultValue('')->comment('管理员姓名'),
            'created_at' => $this->integer(10)->notNull()->defaultValue(0)->comment('创建时间戳，当为0时表示为，用户未自主注册的用户'),
            'updated_at' => $this->integer(10)->unsigned()->notNull()->defaultValue(0)->comment('数据项最后修改时间')
        ], $tableOptions);
        $this->createIndex('username', '{{%user}}', 'username', true);
        $this->createIndex('wechat_open_id', '{{%user}}', 'wechat_open_id', true);
        $this->createIndex('customer_id', '{{%user}}', 'customer_id');
        $this->initRbac();
    }

    private function initRbac()
    {
        $auth = Yii::$app->get('administratorAuthManager');

        $list = $auth->createPermission('user/list');
        $list->description = '客户列表';
        $auth->add($list);

        $create = $auth->createPermission('user/create');
        $create->description = '添加客户';
        $auth->add($create);
        $auth->addChild($create, $list);

        $createVest = $auth->createPermission('user/create_vest');
        $createVest->description = '创建马甲客户';
        $auth->add($createVest);
    }

    public function down()
    {
        echo "m170221_024411_user cannot be reverted.\n";

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
