<?php

use yii\db\Migration;

/**
 * Class m230814_162554_add_game_member_table
 */
class m230814_162554_add_game_member_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $tableName = $this->db->tablePrefix . 'game_member';
        if ($this->db->getTableSchema($tableName, true) !== null) {
            $this->dropTable($tableName);
        }

        $this->createTable('{{%game_member}}', [
            'id' => $this->primaryKey(),
            'game_id' => $this->integer()->notNull()->comment('id игры из таблицы game'),
            'discord_id' => $this->string()->notNull()->comment('discord_id участника'),
            'name' => $this->string()->notNull()->comment('имя участника'),
            'avatar' => $this->string()->notNull()->comment('аватар участника'),
            'slot' => $this->string()->notNull()->comment('слот участника'),
            'role' => $this->string()->notNull()->comment('слот участника'),
            'result' => $this->text()->null()->comment('результат участника в игре')
        ], $tableOptions);

        $this->addForeignKey(
            'fk-member_game_id',
            'game_member',
            'game_id',
            'game',
            'id',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropForeignKey(
            'fk-member_game_id',
            'game_member'
        );
        $this->dropTable('{{%game_member}}');
    }
}
