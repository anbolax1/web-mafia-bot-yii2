<?php

use yii\db\Migration;

/**
 * Class m230921_195045_add_channel_id_to_game_table
 */
class m230921_195045_add_channel_id_to_game_table extends Migration
{
    public function up()
    {
        $this->addColumn('{{%game}}', 'channel_id', $this->string()->null()->comment('идентификатор тексового канала игры'));

    }

    public function down()
    {
        $this->dropColumn('{{%game}}', 'channel_id');
    }
}
