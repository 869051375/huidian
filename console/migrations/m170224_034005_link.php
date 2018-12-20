<?php

use yii\db\Migration;

class m170224_034005_link extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        // 产品数据表
        $this->createTable('{{%link}}', [
            'id' => $this->primaryKey()->unsigned()->comment('友链id'),
            'name' => $this->string(25)->notNull()->defaultValue('')->comment('名称'),
            'image' => $this->string(64)->notNull()->defaultValue('')->comment('图片'),
            'url' => $this->string(255)->notNull()->defaultValue('')->comment('链接'),
            'sort' => $this->integer(11)->notNull()->defaultValue(500)->comment('排序（由小到大）'),
            'creator_id' => $this->integer(11)->notNull()->defaultValue(0)->unsigned()->comment('管理员id'),
            'creator_name' => $this->string(10)->notNull()->defaultValue('')->comment('管理员姓名'),
            'created_at' => $this->integer(10)->notNull()->defaultValue(0)->comment('创建时间戳'),
            'updated_at' => $this->integer(10)->notNull()->defaultValue(0)->unsigned()->comment('最后修改时间'),
        ], $tableOptions);
        $this->initRbac();
    }

    private function initRbac()
    {
        $auth = Yii::$app->get('administratorAuthManager');

        $list = $auth->createPermission('link/list');
        $list->description = '友情链接列表';
        $auth->add($list);

        $create = $auth->createPermission('link/create');
        $create->description = '添加友情链接';
        $auth->add($create);
        $auth->addChild($create, $list);

        $update = $auth->createPermission('link/update');
        $update->description = '修改友情链接';
        $auth->add($update);
        $auth->addChild($update, $list);

        $delete = $auth->createPermission('link/delete');
        $delete->description = '删除友情链接';
        $auth->add($delete);
        $auth->addChild($delete, $list);
    }

    public function down()
    {
        echo "m170224_034005_link cannot be reverted.\n";

        return false;
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
