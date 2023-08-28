<?php

use yii\db\Migration;

/**
 * Class m230827_234659_add_self_video_field_in_channel_member_table
 */
class m230827_234659_add_self_video_field_in_channel_member_table extends Migration
{
    public function up()
    {
        $this->addColumn('{{%channel_member}}', 'self_video', $this->string()->null()->after('avatar')->comment('включена ли вебка участника канала'));

    }

    public function down()
    {
        $this->dropColumn('{{%channel_member}}', 'self_video');
    }
}
