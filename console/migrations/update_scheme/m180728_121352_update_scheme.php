<?php

use yii\db\Migration;

class m180728_121352_update_scheme extends Migration
{
    public function safeUp()
    {
           $this->addColumn('{{%link}}', 'product_category_id',
              $this->integer(11)->notNull()->defaultValue(0)->unsigned()->comment('商品分类id(显示所在页面)')->after('sort'));
           
          $this->addColumn('{{%link}}', 'logo',
              $this->integer(2)->notNull()->defaultValue(1)->unsigned()->comment('1：首页链接 2：列表页链接')->after('creator_name'));
    }

    public function safeDown()
    {
        echo "m180728_121352_update_scheme cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180717_121341_update_scheme cannot be reverted.\n";

        return false;
    }
    */
}
