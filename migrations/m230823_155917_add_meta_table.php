<?php

use yii\db\Migration;

/**
 * Class m230823_155917_add_meta_table
 */
class m230823_155917_add_meta_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $tableName = $this->db->tablePrefix . 'meta';
        if ($this->db->getTableSchema($tableName, true) !== null) {
            $this->dropTable($tableName);
        }

        $this->createTable('{{%meta}}', [
            'id' => $this->primaryKey(),
            'key' => $this->string()->null(),
            'value' => $this->string()->null(),
        ], $tableOptions);
    }

    public function down()
    {
        $this->dropTable('{{%meta}}');
    }
}
