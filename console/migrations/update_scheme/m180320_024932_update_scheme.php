<?php

use yii\db\Migration;

class m180320_024932_update_scheme extends Migration
{
    public function safeUp()
    {
        /** @var \yii\rbac\ManagerInterface $auth */
        $auth = Yii::$app->get('administratorAuthManager');
        $invoiceActionApplyInvoice = $auth->createPermission('invoice-action/apply-invoice');
        $invoiceActionApplyInvoice->description = '申请发票';
        $auth->add($invoiceActionApplyInvoice);
    }

    public function safeDown()
    {
        echo "m180320_024932_update_scheme cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180320_024932_update_scheme cannot be reverted.\n";

        return false;
    }
    */
}
