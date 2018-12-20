<?php

use yii\db\Migration;

class m170222_013238_banner extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }
        // banner焦点图
        $this->createTable('{{%banner}}', [
            'id' => $this->primaryKey()->unsigned(),
            'title' => $this->string(25)->notNull()->defaultValue('')->comment('标题'),
            'image' => $this->string(64)->notNull()->defaultValue('')->comment('图片'),
            'url' => $this->string(255)->notNull()->defaultValue('')->comment('链接'),
            'sort' => $this->integer(11)->notNull()->defaultValue(0)->comment('排序（由小到大）'),
            'pv' => $this->integer(11)->notNull()->defaultValue(0)->comment('PV'),
            'uv' => $this->integer(11)->notNull()->defaultValue(0)->comment('UV'),
            'target' => $this->smallInteger(1)->notNull()->defaultValue(1)->comment('显示目标，1为电脑网页，2为手机网页'),
            'creator_id' => $this->integer(11)->notNull()->defaultValue(0)->unsigned()->comment('管理员id'),
            'creator_name' => $this->string(10)->notNull()->defaultValue('')->comment('管理员姓名'),
            'created_at' => $this->integer(10)->notNull()->defaultValue(0)->unsigned()->comment('创建时间'),
            'updated_at' => $this->integer(10)->notNull()->defaultValue(0)->unsigned()->comment('最后修改时间'),
        ], $tableOptions);
        $this->initRbac();
    }

    private function initRbac()
    {
        /** @var \yii\rbac\ManagerInterface $auth */
        $auth = Yii::$app->get('administratorAuthManager');

        $list = $auth->createPermission('banner/list');
        $list->description = '焦点图列表';
        $auth->add($list);

        $create = $auth->createPermission('banner/create');
        $create->description = '创建焦点图';
        $auth->add($create);
        $auth->addChild($create, $list);

        $update = $auth->createPermission('banner/update');
        $update->description = '修改焦点图';
        $auth->add($update);
        $auth->addChild($update, $list);

        $delete = $auth->createPermission('banner/delete');
        $delete->description = '删除焦点图';
        $auth->add($delete);
        $auth->addChild($delete, $list);

    }

    public function down()
    {
        echo "m170222_013238_banner cannot be reverted.\n";

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
