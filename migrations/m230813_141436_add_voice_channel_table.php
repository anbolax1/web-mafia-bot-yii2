<?php

use yii\db\Migration;

/**
 * Class m230813_141436_add_voice_channel_table
 */
class m230813_141436_add_voice_channel_table extends Migration
{
    public function up()
    {

        $tableOptions = null;
        if($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $tableName = $this->db->tablePrefix . 'voice_channel';
        if ($this->db->getTableSchema($tableName, true) !== null) {
            $this->dropTable($tableName);
        }

        $this->createTable('{{%voice_channel}}', [
            'id' => $this->primaryKey(),
            'discord_id' => $this->string()->notNull(),
            'guild_id' => $this->integer()->notNull(),
            'name' => $this->string()->null(),
        ], $tableOptions);

        $this->addForeignKey(
            'fk-guild_id',
            'voice_channel',
            'guild_id',
            'guild',
            'id',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropForeignKey(
            'fk-guild_id',
            'voice_channel'
        );
        $this->dropTable('{{%voice_channel}}');
    }
}
