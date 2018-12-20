<?php

use yii\db\Migration;

class m170223_111022_partner extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        // 合作客户表据
        $this->createTable('{{%partner}}', [
            'id' => $this->primaryKey()->unsigned()->comment('合作客户id'),
            'name' => $this->string(20)->notNull()->defaultValue('')->comment('合作客户名'),
            'url' => $this->string(255)->notNull()->defaultValue('')->comment('链接'),
            'introduce' => $this->text()->comment('介绍'),
            'image' => $this->string(64)->notNull()->defaultValue('')->comment('图片'),
            'sort' => $this->integer(11)->notNull()->defaultValue(500)->comment('排序（由小到大）'),
            'created_at' => $this->integer(10)->notNull()->defaultValue(0)->unsigned()->comment('创建时间'),
            'updated_at' => $this->integer(10)->notNull()->defaultValue(0)->unsigned()->comment('最后修改时间'),
        ], $tableOptions);
        $this->initRbac();
    }

    private function initRbac()
    {
        /** @var \yii\rbac\ManagerInterface $auth */
        $auth = Yii::$app->get('administratorAuthManager');

        $list = $auth->createPermission('partner/list');
        $list->description = '合作客户列表';
        $auth->add($list);

        $create = $auth->createPermission('partner/create');
        $create->description = '创建合作客户';
        $auth->add($create);
        $auth->addChild($create, $list);

        $update = $auth->createPermission('partner/update');
        $update->description = '修改合作客户';
        $auth->add($update);
        $auth->addChild($update, $list);

        $delete = $auth->createPermission('partner/delete');
        $delete->description = '删除合作客户';
        $auth->add($delete);
        $auth->addChild($delete, $list);
    }

    public function down()
    {
        echo "m170223_111022_partner cannot be reverted.\n";

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
