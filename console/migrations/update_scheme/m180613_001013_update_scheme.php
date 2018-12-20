<?php

use yii\db\Migration;

class m180613_001013_update_scheme extends Migration
{
    public function safeUp()
    {
        /** @var \yii\rbac\ManagerInterface $auth */
        $auth = Yii::$app->get('administratorAuthManager');

        $performance = $auth->createPermission('performance/real-time-data');
        $performance->description = '预计利润表实时数据';
        $auth->add($performance);
    }

    public function safeDown()
    {
        echo "m180522_002406_update_scheme cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180329_022414_update_scheme cannot be reverted.\n";

        return false;
    }
    */
}
