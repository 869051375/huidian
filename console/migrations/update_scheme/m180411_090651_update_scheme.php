<?php

use yii\db\Migration;

class m180411_090651_update_scheme extends Migration
{
    public function safeUp()
    {
        /** @var \yii\rbac\ManagerInterface $auth */
        $auth = Yii::$app->get('administratorAuthManager');

        $import = $auth->createPermission('customer/import');
        $import->description = '客户批量导入';
        $auth->add($import);
    }

    public function safeDown()
    {
        echo "m180411_090651_update_scheme cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180411_090651_update_scheme cannot be reverted.\n";

        return false;
    }
    */
}
