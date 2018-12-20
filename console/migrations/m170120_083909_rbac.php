<?php

use yii\db\Migration;

class m170120_083909_rbac extends Migration
{
    public function up()
    {
    	$this->role();
    }

    private function role()
	{
		/** @var \yii\rbac\ManagerInterface $auth */
		$auth = Yii::$app->get('administratorAuthManager');

		// 管理员角色管理
		$list = $auth->createPermission('role/list');
		$list->description = '管理员角色列表';
		$auth->add($list);

		$create = $auth->createPermission('role/create');
		$create->description = '创建管理员角色';
		$auth->add($create);
		$auth->addChild($create, $list);

		$update = $auth->createPermission('role/update');
		$update->description = '编辑管理员角色';
		$auth->add($update);
		$auth->addChild($update, $list);
	}

    public function down()
    {
        echo "m170120_083909_rbac cannot be reverted.\n";

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
