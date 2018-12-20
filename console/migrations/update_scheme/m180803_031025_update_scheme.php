<?php

use yii\db\Migration;

class m180803_031025_update_scheme extends Migration
{
    public function safeUp()
    {
        /** @var \yii\rbac\ManagerInterface $auth */
        $auth = Yii::$app->get('administratorAuthManager');

        $system = $auth->createPermission('administrator/system');
        $system->description = '系统设置管理';
        $auth->add($system);
    }

    public function safeDown()
    {
        echo "m180803_031025_update_scheme cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180803_031025_update_scheme cannot be reverted.\n";

        return false;
    }
    */
}
