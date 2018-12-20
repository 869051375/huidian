<?php

use yii\db\Migration;
use yii\base\InvalidConfigException;
use yii\rbac\DbManager;

class m170112_021407_backend_rbac extends Migration
{
	/**
	 * @throws yii\base\InvalidConfigException
	 * @return DbManager
	 */
	protected function getAuthManager()
	{
		Yii::$app->setComponents([
			'authManager' => [
				'class' => 'yii\rbac\DbManager',
				'itemTable' => '{{%administrator_auth_item}}',
				'itemChildTable' => '{{%administrator_auth_item_child}}',
				'assignmentTable' => '{{%administrator_auth_assignment}}',
				'ruleTable' => '{{%administrator_auth_rule}}',
			]
		]);
		$authManager = Yii::$app->getAuthManager();
		if (!$authManager instanceof DbManager) {
			throw new InvalidConfigException('You should configure "authManager" component to use database before executing this migration.');
		}
		return $authManager;
	}

	/**
	 * @return bool
	 */
	protected function isMSSQL()
	{
		return $this->db->driverName === 'mssql' || $this->db->driverName === 'sqlsrv' || $this->db->driverName === 'dblib';
	}

	/**
	 * @inheritdoc
	 */
	public function up()
	{
		$authManager = $this->getAuthManager();
		$this->db = $authManager->db;

		$tableOptions = null;
		if ($this->db->driverName === 'mysql') {
			// http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
			$tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
		}

		$this->createTable($authManager->ruleTable, [
			'name' => $this->string(64)->notNull(),
			'data' => $this->text(),
			'created_at' => $this->integer(),
			'updated_at' => $this->integer(),
			'PRIMARY KEY (name)',
		], $tableOptions);

		$this->createTable($authManager->itemTable, [
			'name' => $this->string(64)->notNull(),
			'type' => $this->integer()->notNull(),
			'description' => $this->text(),
			'rule_name' => $this->string(64),
			'data' => $this->text(),
			'created_at' => $this->integer(),
			'updated_at' => $this->integer(),
			'PRIMARY KEY (name)',
		], $tableOptions);
		$this->createIndex('idx-auth_item-type', $authManager->itemTable, 'type');

		$this->createTable($authManager->itemChildTable, [
			'parent' => $this->string(64)->notNull(),
			'child' => $this->string(64)->notNull(),
			'PRIMARY KEY (parent, child)',
		], $tableOptions);

		$this->createTable($authManager->assignmentTable, [
			'item_name' => $this->string(64)->notNull(),
			'user_id' => $this->string(64)->notNull(),
			'created_at' => $this->integer(),
			'PRIMARY KEY (item_name, user_id)',
		], $tableOptions);

		if ($this->isMSSQL()) {
			$this->execute("CREATE TRIGGER dbo.trigger_auth_item_child
            ON dbo.{$authManager->itemTable}
            INSTEAD OF DELETE, UPDATE
            AS
            DECLARE @old_name VARCHAR (64) = (SELECT name FROM deleted)
            DECLARE @new_name VARCHAR (64) = (SELECT name FROM inserted)
            BEGIN
            IF COLUMNS_UPDATED() > 0
                BEGIN
                    IF @old_name <> @new_name
                    BEGIN
                        ALTER TABLE auth_item_child NOCHECK CONSTRAINT FK__auth_item__child;
                        UPDATE auth_item_child SET child = @new_name WHERE child = @old_name;
                    END
                UPDATE auth_item
                SET name = (SELECT name FROM inserted),
                type = (SELECT type FROM inserted),
                description = (SELECT description FROM inserted),
                rule_name = (SELECT rule_name FROM inserted),
                data = (SELECT data FROM inserted),
                created_at = (SELECT created_at FROM inserted),
                updated_at = (SELECT updated_at FROM inserted)
                WHERE name IN (SELECT name FROM deleted)
                IF @old_name <> @new_name
                    BEGIN
                        ALTER TABLE auth_item_child CHECK CONSTRAINT FK__auth_item__child;
                    END
                END
                ELSE
                    BEGIN
                        DELETE FROM dbo.{$authManager->itemChildTable} WHERE parent IN (SELECT name FROM deleted) OR child IN (SELECT name FROM deleted);
                        DELETE FROM dbo.{$authManager->itemTable} WHERE name IN (SELECT name FROM deleted);
                    END
            END;");
		}
	}

    public function down()
    {
        echo "m170112_021407_backend_rbac cannot be reverted.\n";

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
