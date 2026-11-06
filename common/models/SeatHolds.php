<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "seat_holds".
 *
 * @property string $session_id
 * @property string $seat_id
 * @property string $user_id
 * @property string $expires_at
 *
 * @property Seats $seat
 * @property Sessions $session
 * @property Users $user
 */
class SeatHolds extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'seat_holds';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['session_id', 'seat_id', 'user_id', 'expires_at'], 'required'],
            [['session_id', 'seat_id', 'user_id'], 'string'],
            [['expires_at'], 'safe'],
            [['session_id', 'seat_id'], 'unique', 'targetAttribute' => ['session_id', 'seat_id']],
            [['seat_id'], 'exist', 'skipOnError' => true, 'targetClass' => Seats::class, 'targetAttribute' => ['seat_id' => 'id']],
            [['session_id'], 'exist', 'skipOnError' => true, 'targetClass' => Sessions::class, 'targetAttribute' => ['session_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'session_id' => 'Session ID',
            'seat_id' => 'Seat ID',
            'user_id' => 'User ID',
            'expires_at' => 'Expires At',
        ];
    }

    /**
     * Gets query for [[Seat]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSeat()
    {
        return $this->hasOne(Seats::class, ['id' => 'seat_id']);
    }

    /**
     * Gets query for [[Session]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSession()
    {
        return $this->hasOne(Sessions::class, ['id' => 'session_id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Users::class, ['id' => 'user_id']);
    }

}
