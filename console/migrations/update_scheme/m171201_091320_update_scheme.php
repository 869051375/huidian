<?php

use yii\db\Migration;

class m171201_091320_update_scheme extends Migration
{
    public function safeUp()
    {
        /** @var \yii\rbac\ManagerInterface $auth */
        $auth = Yii::$app->get('administratorAuthManager');

        $list = $auth->createPermission('department/list');

        $companyCreate = $auth->createPermission('company/create');
        $companyCreate->description = '新建公司';
        $auth->add($companyCreate);
        $auth->addChild($companyCreate, $list);

        $companyUpdate = $auth->createPermission('company/update');
        $companyUpdate->description = '编辑公司';
        $auth->add($companyUpdate);
        $auth->addChild($companyUpdate, $list);

        $companyDelete = $auth->createPermission('company/delete');
        $companyDelete->description = '删除公司';
        $auth->add($companyDelete);
        $auth->addChild($companyDelete, $list);
    }

    public function safeDown()
    {
        echo "m171201_091320_update_scheme cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171201_091320_update_scheme cannot be reverted.\n";

        return false;
    }
    */
}
