<?php

use yii\db\Migration;

/**
 * Class m230814_162420_add_game_setting_table
 */
class m230814_162420_add_game_setting_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $tableName = $this->db->tablePrefix . 'game_setting';
        if ($this->db->getTableSchema($tableName, true) !== null) {
            $this->dropTable($tableName);
        }

        $this->createTable('{{%game_setting}}', [
            'id' => $this->primaryKey(),
            'game_id' => $this->integer()->notNull()->comment('id игры из таблицы game'),
            'settings' => $this->text()->notNull()->comment('настройки игры'),
        ], $tableOptions);

        $this->addForeignKey(
            'fk-game_id',
            'game_setting',
            'game_id',
            'game',
            'id',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropForeignKey(
            'fk-game_id',
            'game_setting'
        );
        $this->dropTable('{{%game_setting}}');
    }
}
