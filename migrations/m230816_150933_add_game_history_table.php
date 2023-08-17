<?php

use yii\db\Migration;

/**
 * Class m230816_150933_add_game_history_table
 */
class m230816_150933_add_game_history_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $tableName = $this->db->tablePrefix . 'game_history';
        if ($this->db->getTableSchema($tableName, true) !== null) {
            $this->dropTable($tableName);
        }

        $this->createTable('{{%game_history}}', [
            'id' => $this->primaryKey(),
            'game_id' => $this->integer()->notNull()->comment('id игры из таблицы game'),
            'member_discord_id' => $this->string()->null()->comment('discord_id участника'),
            'description' => $this->string()->notNull()->comment('описание'),
            'time' => $this->string()->notNull()->comment('время'),
        ], $tableOptions);

        $this->addForeignKey(
            'fk-history_game_id',
            'game_history',
            'game_id',
            'game',
            'id',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropForeignKey(
            'fk-history_game_id',
            'game_history'
        );
        $this->dropTable('{{%game_history}}');
    }
}
