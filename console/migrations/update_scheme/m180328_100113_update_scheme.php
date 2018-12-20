<?php

use yii\db\Migration;

class m180328_100113_update_scheme extends Migration
{
    public function safeUp()
    {
        /** @var \yii\rbac\ManagerInterface $auth */
        $auth = Yii::$app->get('administratorAuthManager');
        $customerExport = $auth->createPermission('customer/export');
        $customerExport->description = '导出客户记录';
        $auth->add($customerExport);
    }

    public function safeDown()
    {
        echo "m180328_100113_update_scheme cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180328_100113_update_scheme cannot be reverted.\n";

        return false;
    }
    */
}
