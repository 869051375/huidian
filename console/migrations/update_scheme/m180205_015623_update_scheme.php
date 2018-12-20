<?php

use yii\db\Migration;

class m180205_015623_update_scheme extends Migration
{
    public function safeUp()
    {
        /** @var \yii\rbac\ManagerInterface $auth */
        $auth = Yii::$app->get('administratorAuthManager');

        $companyAll = $auth->createPermission('company/all');
        $companyAll->description = '组织机构视图列表';
        $auth->add($companyAll);

        $roleList = $auth->getPermission('role/list');
        $auth->addChild($roleList, $companyAll);

        $departmentList = $auth->getPermission('department/list');
        $departmentList->description = '组织机构管理列表';
        $auth->update($departmentList->description, $departmentList);
        $auth->addChild($departmentList, $companyAll);

        $modifyDepartment = $auth->createPermission('company/department-modify');
        $modifyDepartment->description = '修改人员所属公司与部门';
        $auth->add($modifyDepartment);
    }

    public function safeDown()
    {
        echo "m180205_015623_update_scheme cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180205_015623_update_scheme cannot be reverted.\n";

        return false;
    }
    */
}
