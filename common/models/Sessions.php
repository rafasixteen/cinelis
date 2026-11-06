<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sessions".
 *
 * @property string $id
 * @property string $movie_id
 * @property string $room_id
 * @property string $starts_at
 * @property float $price
 * @property string $status
 *
 * @property Movies $movie
 * @property Rooms $room
 * @property SeatHolds[] $seatHolds
 * @property Seats[] $seats
 * @property Seats[] $seats0
 * @property Tickets[] $tickets
 */
class Sessions extends \yii\db\ActiveRecord
{

    /**
     * ENUM field values
     */
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'sessions';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['status'], 'default', 'value' => 'scheduled'],
            [['id', 'movie_id', 'room_id', 'starts_at', 'price'], 'required'],
            [['id', 'movie_id', 'room_id', 'status'], 'string'],
            [['starts_at'], 'safe'],
            [['price'], 'number'],
            ['status', 'in', 'range' => array_keys(self::optsStatus())],
            [['id'], 'unique'],
            [['movie_id'], 'exist', 'skipOnError' => true, 'targetClass' => Movies::class, 'targetAttribute' => ['movie_id' => 'id']],
            [['room_id'], 'exist', 'skipOnError' => true, 'targetClass' => Rooms::class, 'targetAttribute' => ['room_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'movie_id' => 'Movie ID',
            'room_id' => 'Room ID',
            'starts_at' => 'Starts At',
            'price' => 'Price',
            'status' => 'Status',
        ];
    }

    /**
     * Gets query for [[Movie]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMovie()
    {
        return $this->hasOne(Movies::class, ['id' => 'movie_id']);
    }

    /**
     * Gets query for [[Room]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRoom()
    {
        return $this->hasOne(Rooms::class, ['id' => 'room_id']);
    }

    /**
     * Gets query for [[SeatHolds]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSeatHolds()
    {
        return $this->hasMany(SeatHolds::class, ['session_id' => 'id']);
    }

    /**
     * Gets query for [[Seats]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSeats()
    {
        return $this->hasMany(Seats::class, ['id' => 'seat_id'])->viaTable('seat_holds', ['session_id' => 'id']);
    }

    /**
     * Gets query for [[Seats0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSeats0()
    {
        return $this->hasMany(Seats::class, ['id' => 'seat_id'])->viaTable('tickets', ['session_id' => 'id']);
    }

    /**
     * Gets query for [[Tickets]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTickets()
    {
        return $this->hasMany(Tickets::class, ['session_id' => 'id']);
    }


    /**
     * column status ENUM value labels
     * @return string[]
     */
    public static function optsStatus()
    {
        return [
            self::STATUS_SCHEDULED => 'scheduled',
            self::STATUS_CANCELLED => 'cancelled',
        ];
    }

    /**
     * @return string
     */
    public function displayStatus()
    {
        return self::optsStatus()[$this->status];
    }

    /**
     * @return bool
     */
    public function isStatusScheduled()
    {
        return $this->status === self::STATUS_SCHEDULED;
    }

    public function setStatusToScheduled()
    {
        $this->status = self::STATUS_SCHEDULED;
    }

    /**
     * @return bool
     */
    public function isStatusCancelled()
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function setStatusToCancelled()
    {
        $this->status = self::STATUS_CANCELLED;
    }
}
