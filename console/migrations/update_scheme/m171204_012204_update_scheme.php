<?php

use yii\db\Migration;

class m171204_012204_update_scheme extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('{{%business_subject}}', 'enterprise_type',
            $this->string(20)->notNull()->defaultValue('')->comment('企业类型'));
        $this->alterColumn('{{%business_subject}}', 'scope',
            $this->text()->comment('经营范围'));
    }

    public function safeDown()
    {
        echo "m171204_012204_update_scheme cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171120_030651_update_scheme cannot be reverted.\n";

        return false;
    }
    */
}
