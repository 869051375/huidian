<?php

use yii\db\Migration;

class m171208_103131_update_scheme extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%order}}', 'financial_code',
            $this->string(6)->notNull()->defaultValue('')->comment('财务明细编号')->after('estimate_service_time'));

        $auth = Yii::$app->get('administratorAuthManager');
        $orderInfo = $auth->getPermission('order/info');
        $orderFinancialUpdate = $auth->createPermission('order-action/financial-update');
        $orderFinancialUpdate->description = '修改财务明细编号';
        $auth->add($orderFinancialUpdate);
        $auth->addChild($orderFinancialUpdate, $orderInfo);
    }

    public function safeDown()
    {
        echo "m171208_103131_update_scheme cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171208_103131_update_scheme cannot be reverted.\n";

        return false;
    }
    */
}
