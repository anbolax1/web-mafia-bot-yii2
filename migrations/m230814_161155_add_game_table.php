<?php

use yii\db\Migration;

/**
 * Class m230814_161155_add_game_table
 */
class m230814_161155_add_game_table extends Migration
{
    public function up()
    {

        $tableOptions = null;
        if($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $tableName = $this->db->tablePrefix . 'game';
        if ($this->db->getTableSchema($tableName, true) !== null) {
            $this->dropTable($tableName);
        }

        $this->createTable('{{%game}}', [
            'id' => $this->primaryKey(),
            'host_id' => $this->integer()->notNull()->comment('id ведущего из таблицы user'),
            'guild_id' => $this->string()->notNull()->comment('id сервера дискорда, на котором проводилась игра'),
            'status' => $this->string()->notNull()->comment('статус игры'),
            'win_role' => $this->string()->null()->comment('кто победил'),
            'start_time' => $this->string()->null(),
            'end_time' => $this->string()->null(),
        ], $tableOptions);

        $this->addForeignKey(
            'fk-host_id',
            'game',
            'host_id',
            'user',
            'id',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropForeignKey(
            'fk-host_id',
            'game'
        );
        $this->dropTable('{{%game}}');
    }
}
