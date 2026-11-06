<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "rooms".
 *
 * @property string $id
 * @property string $name
 * @property string|null $layout
 *
 * @property Seats[] $seats
 * @property Sessions[] $sessions
 */
class Rooms extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'rooms';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['layout'], 'default', 'value' => null],
            [['id', 'name'], 'required'],
            [['id', 'name'], 'string'],
            [['layout'], 'safe'],
            [['id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'layout' => 'Layout',
        ];
    }

    /**
     * Gets query for [[Seats]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSeats()
    {
        return $this->hasMany(Seats::class, ['room_id' => 'id']);
    }

    /**
     * Gets query for [[Sessions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSessions()
    {
        return $this->hasMany(Sessions::class, ['room_id' => 'id']);
    }

}
