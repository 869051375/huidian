<?php

use yii\db\Migration;

class m170227_015346_holidays extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        // 合作客户表据
        $this->createTable('{{%holidays}}', [
            'year' => $this->primaryKey()->unsigned()->comment('年度，主键，唯一值'),
            'holidays' => $this->text()->comment('年度休息日,逗号分割'),
        ], $tableOptions);
        $this->initRbac();
    }

    private function initRbac()
    {
        /** @var \yii\rbac\ManagerInterface $auth */
        $auth = Yii::$app->get('administratorAuthManager');

        $list = $auth->createPermission('holidays/list');
        $list->description = '查看年度工作日';
        $auth->add($list);

        $create = $auth->createPermission('holidays/create');
        $create->description = '创建年度工作日';
        $auth->add($create);
        $auth->addChild($create, $list);

        $update = $auth->createPermission('holidays/update');
        $update->description = '修改年度工作日';
        $auth->add($update);
        $auth->addChild($update, $list);

        $update = $auth->createPermission('holidays/delete');
        $update->description = '删除年度工作日';
        $auth->add($update);
        $auth->addChild($update, $list);
    }


    public function down()
    {
        echo "m170227_015346_holidays cannot be reverted.\n";

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
