<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "tickets".
 *
 * @property string $id
 * @property string $order_id
 * @property string $session_id
 * @property string $seat_id
 * @property float $price
 * @property string $code
 * @property string $created_at
 * @property string|null $used_at
 *
 * @property Order $order
 * @property Seat $seat
 * @property Session $session
 */
class Ticket extends \yii\db\ActiveRecord
{
	/**
	 * {@inheritdoc}
	 */
	public static function tableName()
	{
		return 'tickets';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules()
	{
		return [
			[['used_at'], 'default', 'value' => null],
			[['id', 'order_id', 'session_id', 'seat_id', 'price', 'code', 'created_at'], 'required'],
			[['id', 'order_id', 'session_id', 'seat_id', 'code'], 'string'],
			[['price'], 'number'],
			[['created_at', 'used_at'], 'safe'],
			[['code'], 'unique'],
			[['session_id', 'seat_id'], 'unique', 'targetAttribute' => ['session_id', 'seat_id']],
			[['id'], 'unique'],
			[['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Order::class, 'targetAttribute' => ['order_id' => 'id']],
			[['seat_id'], 'exist', 'skipOnError' => true, 'targetClass' => Seat::class, 'targetAttribute' => ['seat_id' => 'id']],
			[['session_id'], 'exist', 'skipOnError' => true, 'targetClass' => Session::class, 'targetAttribute' => ['session_id' => 'id']],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels()
	{
		return [
			'id' => 'ID',
			'order_id' => 'Order ID',
			'session_id' => 'Session ID',
			'seat_id' => 'Seat ID',
			'price' => 'Price',
			'code' => 'Code',
			'created_at' => 'Created At',
			'used_at' => 'Used At',
		];
	}

	/**
	 * Gets query for [[Order]].
	 *
	 * @return \yii\db\ActiveQuery
	 */
	public function getOrder()
	{
		return $this->hasOne(Order::class, ['id' => 'order_id']);
	}

	/**
	 * Gets query for [[Seat]].
	 *
	 * @return \yii\db\ActiveQuery
	 */
	public function getSeat()
	{
		return $this->hasOne(Seat::class, ['id' => 'seat_id']);
	}

	/**
	 * Gets query for [[Session]].
	 *
	 * @return \yii\db\ActiveQuery
	 */
	public function getSession()
	{
		return $this->hasOne(Session::class, ['id' => 'session_id']);
	}
}
