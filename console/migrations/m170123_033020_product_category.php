<?php

use yii\db\Migration;

class m170123_033020_product_category extends Migration
{
    public function up()
    {
		$tableOptions = null;
		if ($this->db->driverName === 'mysql') {
			// http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
			$tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
		}

		// 产品分类表
		$this->createTable('{{%product_category}}', [
			'id' => $this->primaryKey()->unsigned(),
			'name' => $this->string(10)->notNull()->defaultValue(''),
            'title' => $this->string(80)->notNull()->defaultValue(''),
            'image' => $this->string(64)->notNull()->defaultValue('')->comment('图片'),
            'icon_image' => $this->string(64)->notNull()->defaultValue('')->comment('图标图片'),
            'banner_image' => $this->string(64)->notNull()->defaultValue('')->comment('banner图片'),
            'banner_url' => $this->string(255)->notNull()->defaultValue('')->comment('banner链接'),
            'is_show_nav' => $this->boolean()->notNull()->defaultValue(0)->comment('0:不显示，1：显示'),
            'is_show_list' => $this->boolean()->notNull()->defaultValue(0)->comment('(0:不显示在列表页，1:显示在列表页，默认0)'),
            'keywords' => $this->string(140)->notNull()->defaultValue(''),
			'description' => $this->text()->notNull(),
            'customer_service_link' => $this->text()->notNull()->comment('客服链接'),
			'parent_id' => $this->integer(11)->unsigned()->notNull()->defaultValue(0),
			'sort' => $this->integer(11)->notNull()->defaultValue(0),
			'status' => $this->boolean()->notNull()->defaultValue(0)->comment('0:停用，1:启用'),
			'created_at' => $this->integer()->unsigned()->notNull(),
			'updated_at' => $this->integer()->unsigned()->notNull(),
		], $tableOptions);

		$this->initRbac();
    }

    private function initRbac()
    {
        /** @var \yii\rbac\ManagerInterface $auth */
        $auth = Yii::$app->get('administratorAuthManager');

        $list = $auth->createPermission('product-category/list');
        $list->description = '商品分类列表';
        $auth->add($list);

        $create = $auth->createPermission('product-category/create');
        $create->description = '创建商品分类';
        $auth->add($create);
        $auth->addChild($create, $list);

        $update = $auth->createPermission('product-category/update');
        $update->description = '修改商品分类';
        $auth->add($update);
        $auth->addChild($update, $list);

        $delete = $auth->createPermission('product-category/delete');
        $delete->description = '删除商品分类';
        $auth->add($delete);
        $auth->addChild($delete, $list);
    }

    public function down()
    {
        echo "m170123_033020_product_category cannot be reverted.\n";

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
