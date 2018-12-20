<?php

use yii\db\Migration;

class m171204_065955_update_scheme extends Migration
{
    public function safeUp()
    {
        /** @var \yii\rbac\ManagerInterface $auth */
        $auth = Yii::$app->get('administratorAuthManager');

        $createOpportunity = $auth->createPermission('opportunity/create-all-customer');
        $createOpportunity->description = '新建全部客户商机';
        $auth->add($createOpportunity);
    }

    public function safeDown()
    {
        echo "m171204_065955_update_scheme cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171204_065955_update_scheme cannot be reverted.\n";

        return false;
    }
    */
}
