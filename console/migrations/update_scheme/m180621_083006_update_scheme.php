<?php

use yii\db\Migration;

class m180621_083006_update_scheme extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%order_file}}', 'is_internal' ,
            $this->boolean()->notNull()->defaultValue(0)->comment('是否仅内部后台查看(0:否，1是，默认0)')->after('creator_name'));

        /** @var \yii\rbac\ManagerInterface $auth */
        $auth = Yii::$app->get('administratorAuthManager');
        $upload = $auth->createPermission('order-action/upload');
        $upload->description = '文件上传';
        $auth->add($upload);
    }

    public function safeDown()
    {
        echo "m180621_083006_update_scheme cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180621_083006_update_scheme cannot be reverted.\n";

        return false;
    }
    */
}
