<?php

use yii\db\Migration;

class m171127_071353_company extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%company}}', [
            'id' => $this->primaryKey()->unsigned()->comment('公司id'),
            'name' => $this->string(6)->notNull()->defaultValue('')->comment('公司名称'),
            'financial_id' => $this->integer(11)->notNull()->defaultValue(0)->comment('财务人员id，administrator表id'),
            'creator_id' => $this->integer(11)->notNull()->defaultValue(0)->unsigned()->comment('创建人id'),
            'creator_name' => $this->string(10)->notNull()->defaultValue('')->comment('创建人姓名'),
            'updater_id' => $this->integer(11)->notNull()->defaultValue(0)->unsigned()->comment('更新人id'),
            'updater_name' => $this->string(10)->notNull()->defaultValue('')->comment('更新人姓名'),
            'created_at' => $this->integer(10)->notNull()->defaultValue(0)->unsigned()->comment('创建时间戳'),
            'updated_at' => $this->integer(10)->notNull()->defaultValue(0)->unsigned()->comment('最后修改时间戳'),
        ], $tableOptions);
    }

    public function safeDown()
    {
        echo "m171127_071353_company cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171127_071353_company cannot be reverted.\n";

        return false;
    }
    */
}
