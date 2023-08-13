<?php

use yii\db\Migration;

/**
 * Class m230813_141748_channel_member
 */
class m230813_141748_channel_member extends Migration
{
    public function up()
    {

        $tableOptions = null;
        if($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $tableName = $this->db->tablePrefix . 'channel_member';
        if ($this->db->getTableSchema($tableName, true) !== null) {
            $this->dropTable($tableName);
        }

        $this->createTable('{{%channel_member}}', [
            'id' => $this->primaryKey(),
            'discord_id' => $this->string()->notNull(),
            'name' => $this->string()->null(),
            'avatar' => $this->string()->null(),
            'channel_id' => $this->integer()->notNull(),
            'flag' => $this->string()
        ], $tableOptions);

        $this->addForeignKey(
            'fk-channel_id',
            'channel_member',
            'channel_id',
            'voice_channel',
            'id',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropForeignKey(
            'fk-channel_id',
            'channel_member'
        );
        $this->dropTable('{{%channel_member}}');
    }
}
