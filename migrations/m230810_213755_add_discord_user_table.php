<?php

use yii\db\Migration;

/**
 * Class m230810_213755_add_discord_user_table
 */
class m230810_213755_add_discord_user_table extends Migration
{
    public function up()
    {

        $tableOptions = null;
        if($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $tableName = $this->db->tablePrefix . 'discord_user';
        if ($this->db->getTableSchema($tableName, true) !== null) {
            $this->dropTable($tableName);
        }

        $this->createTable('{{%discord_user}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(),
            'discord_id' => $this->string()->notNull()->unique(),
            'username' => $this->string(32)->notNull(),
            'avatar' => $this->string()->notNull(),
        ], $tableOptions);

        $this->addForeignKey(
            'fk-user_id',
            'discord_user',
            'user_id',
            'user',
            'id',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropForeignKey(
            'fk-user_id',
            'discord_user'
        );
        $this->dropTable('{{%discord_user}}');
    }
}
