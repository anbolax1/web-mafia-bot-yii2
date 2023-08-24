<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "meta".
 *
 * @property int $id
 * @property string|null $key
 * @property string|null $value
 * @property string|null $timestamp
 */
class Meta extends \yii\db\ActiveRecord
{
    const IS_UPDATE_CHANNEL_MEMBERS = 'is_update_channel_members';
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'meta';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['key', 'value', 'timestamp'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'key' => 'Key',
            'value' => 'Value',
        ];
    }
}
