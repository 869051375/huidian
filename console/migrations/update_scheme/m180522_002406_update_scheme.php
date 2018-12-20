<?php

use yii\db\Migration;

class m180522_002406_update_scheme extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%order_cost_record}}', 'type',
            $this->integer(10)->notNull()->defaultValue(0)->unsigned()->comment('成本类型，默认0录入，1计算')->after('day'));
        
        $this->addColumn('{{%performance_statistics}}', 'type',
            $this->integer(10)->notNull()->defaultValue(0)->unsigned()->comment('计算类型,默认0,(0:常规计算,1:更正计算)')->after('month'));
        $this->addColumn('{{%performance_statistics}}', 'algorithm_type',
            $this->integer(10)->notNull()->defaultValue(0)->unsigned()->comment('算法类型,默认0,(0:阶梯算法,1:固定点位)')->after('type'));
        $this->addColumn('{{%performance_record}}', 'correct_price',
            $this->decimal(15,2)->notNull()->defaultValue(0)->comment('更正金额')->after('calculated_performance'));
        $this->addColumn('{{%performance_statistics}}', 'title',
            $this->string(10)->notNull()->defaultValue('')->comment('名称')->after('algorithm_type'));
        $this->addColumn('{{%performance_statistics}}', 'remark',
            $this->string(30)->notNull()->defaultValue('')->comment('备注')->after('title'));
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
