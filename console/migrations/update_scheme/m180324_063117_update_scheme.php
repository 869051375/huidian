<?php

use yii\db\Migration;

class m180324_063117_update_scheme extends Migration
{
    public function safeUp()
    {
        /** @var \yii\rbac\ManagerInterface $auth */
        $auth = Yii::$app->get('administratorAuthManager');
        $opportunityExport = $auth->createPermission('opportunity/export');
        $opportunityExport->description = '导出商机记录';
        $auth->add($opportunityExport);

        $opportunityConfirmClaim = $auth->createPermission('opportunity-public/confirm-claim');
        $opportunityConfirmClaim->description = '公海商机提取';
        $auth->add($opportunityConfirmClaim);
    }

    public function safeDown()
    {
        echo "m180324_063117_update_scheme cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180324_063117_update_scheme cannot be reverted.\n";

        return false;
    }
    */
}
