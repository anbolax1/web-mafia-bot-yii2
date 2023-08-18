<?php

use yii\db\Migration;

/**
 * Class m230817_135516_add_member_rating_table
 */
class m230817_135516_add_member_rating_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $tableName = $this->db->tablePrefix . 'member_rating';
        if ($this->db->getTableSchema($tableName, true) !== null) {
            $this->dropTable($tableName);
        }

        $this->createTable('{{%member_rating}}', [
            'id' => $this->primaryKey(),
            'discord_id' => $this->string()->notNull()->comment('discord_id участника'),
            'type' => $this->string()->notNull()->comment('тип рейтинга'),
            'guild_id' => $this->string()->null()->comment('id сервера, если рейтинг серверный'),
            'rating' => $this->string()->notNull()->comment('рейтинг'),
        ], $tableOptions);
    }

    public function down()
    {
        $this->dropTable('{{%member_rating}}');
    }
}
