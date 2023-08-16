<?php

use yii\db\Migration;

/**
 * Class m230813_121339_add_guilds_table
 */
class m230813_121339_add_guilds_table extends Migration
{
    public function up()
    {

        $tableOptions = null;
        if($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $tableName = $this->db->tablePrefix . 'guild';
        if ($this->db->getTableSchema($tableName, true) !== null) {
            $this->dropTable($tableName);
        }

        $this->createTable('{{%guild}}', [
            'id' => $this->primaryKey(),
            'discord_id' => $this->string()->notNull()->unique(),
            'name' => $this->string()->null(),
            'voice_channels' => $this->text()->null(),
            'status' => $this->string()->null(),
            'information' => $this->text()->null(),
        ], $tableOptions);
    }

    public function down()
    {
        $this->dropTable('{{%guild}}');
    }
}
