<?php

use yii\db\Migration;

class m180517_101755_update_scheme extends Migration
{
    public function safeUp()
    {
        /** @var \yii\rbac\ManagerInterface $auth */
        $auth = Yii::$app->get('administratorAuthManager');

        $forcedDeal = $auth->createPermission('opportunity/forced-deal');
        $forcedDeal->description = '商机强制成交';
        $auth->add($forcedDeal);
    }

    public function safeDown()
    {
        echo "m180517_101755_update_scheme cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180517_101755_update_scheme cannot be reverted.\n";

        return false;
    }
    */
}
