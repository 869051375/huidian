<?php

use yii\db\Migration;

class m180416_100113_update_scheme extends Migration
{
    public function safeUp()
    {
        /** @var \yii\rbac\ManagerInterface $auth */
        $auth = Yii::$app->get('administratorAuthManager');
        $setting = $auth->createPermission('setting/other-refund-time');
        $setting->description = '第三方平台退款限制时间修改';
        $auth->add($setting);
    }

    public function safeDown()
    {
        echo "m180416_100113_update_scheme cannot be reverted.\n";

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
