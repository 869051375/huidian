<?php

use yii\db\Migration;

class m180208_030943_update_scheme extends Migration
{
    public function safeUp()
    {
        /** @var \yii\rbac\ManagerInterface $auth */
        $auth = Yii::$app->get('administratorAuthManager');

        //去除管理员列表关系
        $listManager = $auth->getPermission('administrator/list-manager');
        $addManager = $auth->getPermission('administrator/add-manager');
        $updateManager = $auth->getPermission('administrator/update-manager');
        $statusManager = $auth->getPermission('administrator/status-manager');
        if($listManager)
        {
            if($auth->hasChild($addManager, $listManager))
            {
                $auth->removeChild($addManager, $listManager);
            }
            if($auth->hasChild($updateManager, $listManager))
            {
                $auth->removeChild($updateManager, $listManager);
            }
            if($auth->hasChild($statusManager, $listManager))
            {
                $auth->removeChild($statusManager, $listManager);
            }
        }

        //去除客服列表关系
        $listCustomerService = $auth->getPermission('administrator/list-customer-service');
        $addCustomerService = $auth->getPermission('administrator/add-customer-service');
        $updateCustomerService = $auth->getPermission('administrator/update-customer-service');
        $statusCustomerService = $auth->getPermission('administrator/status-customer-service');
        if($listCustomerService)
        {
            if($auth->hasChild($addCustomerService, $listCustomerService))
            {
                $auth->removeChild($addCustomerService, $listCustomerService);
            }
            if($auth->hasChild($updateCustomerService, $listCustomerService))
            {
                $auth->removeChild($updateCustomerService, $listCustomerService);
            }
            if($auth->hasChild($statusCustomerService, $listCustomerService))
            {
                $auth->removeChild($statusCustomerService, $listCustomerService);
            }
        }

        //去除嘟嘟妹列表关系
        $listSupervisor = $auth->getPermission('administrator/list-supervisor');
        $addSupervisor = $auth->getPermission('administrator/add-supervisor');
        $updateSupervisor = $auth->getPermission('administrator/update-supervisor');
        $statusSupervisor = $auth->getPermission('administrator/status-supervisor');
        if($listSupervisor)
        {
            if($auth->hasChild($addSupervisor, $listSupervisor))
            {
                $auth->removeChild($addSupervisor, $listSupervisor);
            }
            if($auth->hasChild($updateSupervisor, $listSupervisor))
            {
                $auth->removeChild($updateSupervisor, $listSupervisor);
            }
            if($auth->hasChild($statusSupervisor, $listSupervisor))
            {
                $auth->removeChild($statusSupervisor, $listSupervisor);
            }
        }

        //去除服务人员列表关系
        $listClerk = $auth->getPermission('administrator/list-clerk');
        $addClerk = $auth->getPermission('administrator/add-clerk');
        $updateClerk = $auth->getPermission('administrator/update-clerk');
        $statusClerk = $auth->getPermission('administrator/status-clerk');
        if($listClerk)
        {
            if($auth->hasChild($addClerk, $listClerk))
            {
                $auth->removeChild($addClerk, $listClerk);
            }
            if($auth->hasChild($updateClerk, $listClerk))
            {
                $auth->removeChild($updateClerk, $listClerk);
            }
            if($auth->hasChild($statusClerk, $listClerk))
            {
                $auth->removeChild($statusClerk, $listClerk);
            }
        }

        //去除业务员列表关系
        $listSalesman = $auth->getPermission('administrator/list-salesman');
        $addSalesman = $auth->getPermission('administrator/add-salesman');
        $updateSalesman = $auth->getPermission('administrator/update-salesman');
        $statusSalesman = $auth->getPermission('administrator/status-salesman');
        if($listSalesman)
        {
            if($auth->hasChild($addSalesman, $listSalesman))
            {
                $auth->removeChild($addSalesman, $listSalesman);
            }
            if($auth->hasChild($updateSalesman, $listSalesman))
            {
                $auth->removeChild($updateSalesman, $listSalesman);
            }
            if($auth->hasChild($statusSalesman, $listSalesman))
            {
                $auth->removeChild($statusSalesman, $listSalesman);
            }
        }

        $roleList = $auth->getPermission('role/list');
        $companyAll = $auth->getPermission('company/all');
        if($companyAll && $roleList)
        {
            if($auth->hasChild($roleList, $companyAll))
            {
                $auth->removeChild($roleList, $companyAll);
            }
        }
    }

    public function safeDown()
    {
        echo "m180208_030943_update_scheme cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180208_030943_update_scheme cannot be reverted.\n";

        return false;
    }
    */
}
