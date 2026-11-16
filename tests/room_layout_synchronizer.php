<?php

declare(strict_types=1);

require dirname(__DIR__).'/vendor/autoload.php';
require dirname(__DIR__).'/vendor/yiisoft/yii2/Yii.php';
require dirname(__DIR__).'/common/config/bootstrap.php';

$config = require dirname(__DIR__).'/console/config/main.php';
new yii\console\Application($config);

use common\models\Room;
use common\models\Seat;
use common\services\RoomLayoutSynchronizer;

if (!class_exists(RoomLayoutSynchronizer::class)) {
	fwrite(STDERR, "FAIL: RoomLayoutSynchronizer is not implemented\n");
	exit(1);
}

$db = Yii::$app->db;
$failures = 0;

$assert = static function (bool $condition, string $message): void {
	if (!$condition) {
		throw new RuntimeException($message);
	}
};

$createRoom = static function (string $name): Room {
	$id = Yii::$app->db->createCommand(
		'INSERT INTO rooms (name, layout) VALUES (:name, CAST(:layout AS jsonb)) RETURNING id',
	)->bindValues([
		':name' => $name,
		':layout' => '{}',
	])->queryScalar();

	return Room::findOne($id);
};

$baseLayout = static function (int $columns = 3): array {
	return [
		'version' => 1,
		'grid' => ['rows' => 2, 'columns' => $columns],
		'screen' => [
			'label' => 'Ecrã',
			'row' => 0,
			'column' => 0,
			'rowSpan' => 1,
			'columnSpan' => $columns,
		],
		'entrances' => [['id' => 'entrance-main', 'label' => 'Entrada', 'row' => 3, 'column' => 0]],
		'exits' => [['id' => 'exit-main', 'label' => 'Saída', 'row' => 3, 'column' => $columns - 1]],
		'cells' => [
			['row' => 1, 'column' => 1, 'type' => 'seat'],
			['row' => 1, 'column' => 2, 'type' => 'aisle'],
		],
	];
};

$run = static function (string $name, callable $case) use (&$failures, $db): void {
	$transaction = $db->beginTransaction();

	try {
		$case();
		echo "PASS: {$name}\n";
	} catch (Throwable $error) {
		$failures++;
		fwrite(STDERR, "FAIL: {$name}: {$error->getMessage()}\n");
	} finally {
		if ($transaction->isActive) {
			$transaction->rollBack();
		}
	}
};

$run('creates seats and preserves layout metadata', static function () use ($createRoom, $baseLayout, $assert): void {
	$room = $createRoom('Layout test '.uniqid());
	$layout = RoomLayoutSynchronizer::sync($room, $baseLayout());
	$seatCell = $layout['cells'][0];

	$assert(isset($seatCell['seatId']) && is_string($seatCell['seatId']), 'seat cell must receive a UUID');
	$assert($layout['screen']['label'] === 'Ecrã', 'screen metadata must be retained');
	$assert($layout['entrances'][0]['id'] === 'entrance-main', 'entrance metadata must be retained');
	$assert($layout['exits'][0]['id'] === 'exit-main', 'exit metadata must be retained');
	$assert(Seat::find()->where(['room_id' => $room->id])->count() === 1, 'one seat must be persisted');
	$assert(
		$room->refresh()
		&& is_array($room->layout)
		&& $room->layout['screen']['label'] === 'Ecrã',
		'room layout must be persisted as a JSON object',
	);
});

$run('preserves seat UUIDs while adding new cells', static function () use ($createRoom, $baseLayout, $assert): void {
	$room = $createRoom('Stable seat test '.uniqid());
	$first = RoomLayoutSynchronizer::sync($room, $baseLayout());
	$firstSeatId = $first['cells'][0]['seatId'];

	$next = $baseLayout();
	$next['cells'][] = ['row' => 2, 'column' => 1, 'type' => 'seat'];
	$second = RoomLayoutSynchronizer::sync($room, $next);

	$assert($second['cells'][0]['seatId'] === $firstSeatId, 'existing seat UUID must remain stable');
	$assert(Seat::find()->where(['room_id' => $room->id])->count() === 2, 'new seat cell must create a second seat');
});

$run('removes an unused seat', static function () use ($createRoom, $baseLayout, $assert): void {
	$room = $createRoom('Removal test '.uniqid());
	$first = RoomLayoutSynchronizer::sync($room, $baseLayout(3));
	$next = $baseLayout(3);
	$next['cells'] = [$first['cells'][0]];

	RoomLayoutSynchronizer::sync($room, $next);

	$assert(Seat::find()->where(['room_id' => $room->id])->count() === 1, 'unused removed seat must be deleted');
});

$run('rejects removal of a seat with a protected reference', static function () use ($createRoom, $baseLayout, $assert): void {
	$room = $createRoom('Protected seat test '.uniqid());
	$layout = $baseLayout(3);
	$layout['cells'][] = ['row' => 2, 'column' => 1, 'type' => 'seat'];
	$created = RoomLayoutSynchronizer::sync($room, $layout);
	$protectedSeatId = $created['cells'][0]['seatId'];
	$context = [
		'user' => Yii::$app->db->createCommand(
			'INSERT INTO "user" (username, auth_key, password_hash, email, status, created_at, updated_at) '
			.'VALUES (:username, :authKey, :passwordHash, :email, 10, EXTRACT(EPOCH FROM CURRENT_TIMESTAMP)::integer, '
			.'EXTRACT(EPOCH FROM CURRENT_TIMESTAMP)::integer) RETURNING id',
		)->bindValues([
			':username' => 'layout-user-'.uniqid(),
			':authKey' => 'layout-auth-'.uniqid(),
			':passwordHash' => 'layout-password',
			':email' => 'layout-'.uniqid().'@example.test',
		])->queryScalar(),
	];
	$context['movie'] = Yii::$app->db->createCommand(
		'INSERT INTO movies (title, duration_minutes) VALUES (:title, 90) RETURNING id',
	)->bindValue(':title', 'Layout movie '.uniqid())->queryScalar();
	$context['session'] = Yii::$app->db->createCommand(
		"INSERT INTO sessions (movie_id, room_id, starts_at, price, status) "
		.'VALUES (:movie, :room, CURRENT_TIMESTAMP, 7.50, \'scheduled\') RETURNING id',
	)->bindValues([
		':movie' => $context['movie'],
		':room' => $room->id,
	])->queryScalar();

	Yii::$app->db->createCommand(
		"INSERT INTO seat_holds (session_id, seat_id, user_id, expires_at) "
		."VALUES (:session, :seat, :user, CURRENT_TIMESTAMP + INTERVAL '1 hour')",
	)->bindValues([
		':session' => $context['session'],
		':seat' => $protectedSeatId,
		':user' => $context['user'],
	])->execute();

	$reduced = $layout;
	$reduced['cells'] = [$created['cells'][1]];

	$thrown = false;

	try {
		RoomLayoutSynchronizer::sync($room, $reduced);
	} catch (DomainException) {
		$thrown = true;
	}

	$assert($thrown, 'removing a held seat must reject the update');
	$assert(Seat::findOne($protectedSeatId) !== null, 'rejected update must preserve the held seat');
});

exit($failures === 0 ? 0 : 1);
