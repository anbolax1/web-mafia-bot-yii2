<?php

use yii\db\Migration;

/**
 * Class m230817_141213_add_member_rating_history_table
 */
class m230817_141213_add_member_rating_history_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $tableName = $this->db->tablePrefix . 'member_rating_history';
        if ($this->db->getTableSchema($tableName, true) !== null) {
            $this->dropTable($tableName);
        }

        $this->createTable('{{%member_rating_history}}', [
            'id' => $this->primaryKey(),
            'discord_id' => $this->string()->notNull()->comment('discord_id участника'),
            'game_id' => $this->integer()->notNull()->comment('id игры'),
            'type' => $this->string()->notNull()->comment('тип рейтинга'),
            'guild_id' => $this->string()->null()->comment('id сервера, если рейтинг серверный'),
            'change_rating' => $this->string()->notNull()->comment('изменение рейтинга'),
        ], $tableOptions);

        $this->addForeignKey(
            'fk-rating_history_game_id',
            'member_rating_history',
            'game_id',
            'game',
            'id',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropForeignKey(
            'fk-rating_history_game_id',
            'member_rating_history'
        );
        $this->dropTable('{{%member_rating_history}}');
    }
}
