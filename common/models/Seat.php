<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "seats".
 *
 * @property string $id
 * @property string $room_id
 * @property string $row
 * @property int $number
 *
 * @property Room $room
 * @property SeatHold[] $seatHolds
 * @property Session[] $sessions
 * @property Session[] $sessions0
 * @property Ticket[] $tickets
 */
class Seat extends \yii\db\ActiveRecord
{
	/**
	 * {@inheritdoc}
	 */
	public static function tableName()
	{
		return 'seats';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules()
	{
		return [
			[['id', 'room_id', 'row', 'number'], 'required'],
			[['id', 'room_id', 'row'], 'string'],
			[['number'], 'default', 'value' => null],
			[['number'], 'integer'],
			[['room_id', 'row', 'number'], 'unique', 'targetAttribute' => ['room_id', 'row', 'number']],
			[['id'], 'unique'],
			[['room_id'], 'exist', 'skipOnError' => true, 'targetClass' => Room::class, 'targetAttribute' => ['room_id' => 'id']],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels()
	{
		return [
			'id' => 'ID',
			'room_id' => 'Room ID',
			'row' => 'Row',
			'number' => 'Number',
		];
	}

	/**
	 * Gets query for [[Room]].
	 *
	 * @return \yii\db\ActiveQuery
	 */
	public function getRoom()
	{
		return $this->hasOne(Room::class, ['id' => 'room_id']);
	}

	/**
	 * Gets query for [[SeatHold]].
	 *
	 * @return \yii\db\ActiveQuery
	 */
	public function getSeatHolds()
	{
		return $this->hasMany(SeatHold::class, ['seat_id' => 'id']);
	}

	/**
	 * Gets query for [[Session]].
	 *
	 * @return \yii\db\ActiveQuery
	 */
	public function getSessions()
	{
		return $this->hasMany(Session::class, ['id' => 'session_id'])->viaTable('seat_holds', ['seat_id' => 'id']);
	}

	/**
	 * Gets query for [[Sessions0]].
	 *
	 * @return \yii\db\ActiveQuery
	 */
	public function getSessions0()
	{
		return $this->hasMany(Session::class, ['id' => 'session_id'])->viaTable('tickets', ['seat_id' => 'id']);
	}

	/**
	 * Gets query for [[Ticket]].
	 *
	 * @return \yii\db\ActiveQuery
	 */
	public function getTickets()
	{
		return $this->hasMany(Ticket::class, ['seat_id' => 'id']);
	}
}
