<?php

declare(strict_types=1);

namespace common\services;

use common\models\Room;
use common\models\Seat;
use common\models\SeatHold;
use common\models\Ticket;
use Yii;
use yii\db\Expression;
use yii\db\JsonExpression;

final class RoomLayoutSynchronizer
{
	/**
	 * Normalizes and persists a room layout while synchronizing its seats.
	 *
	 * Seat identities are matched by their logical row/number coordinates. The
	 * layout stores the resulting UUID so subsequent edits keep stable seat IDs.
	 * All changes happen in one database transaction, including the room JSONB
	 * update and seat inserts/deletes.
	 *
	 * @return array<string, mixed> normalized layout with persisted seat UUIDs
	 */
	public static function sync(Room $room, array $layout): array
	{
		return Yii::$app->db->transaction(static function () use ($room, $layout): array {
			$roomId = Yii::$app->db->createCommand(
				'SELECT id FROM rooms WHERE id = :id FOR UPDATE',
			)->bindValue(':id', $room->getPrimaryKey())->queryScalar();

			if ($roomId === false) {
				throw new \DomainException('Room no longer exists.');
			}

			$normalized = self::normalize($layout);
			$existing = Seat::find()
				->where(['room_id' => $roomId])
				->indexBy(static fn (Seat $seat): string => self::seatKey($seat->row, (int) $seat->number))
				->all();
			$desired = [];

			foreach ($normalized['cells'] as $index => $cell) {
				if ($cell['type'] !== 'seat') {
					continue;
				}

				$key = self::seatKeyFromCell($cell);

				if (isset($desired[$key])) {
					throw new \DomainException("Duplicate seat coordinate: {$key}");
				}

				$seat = $existing[$key] ?? null;
				$requestedId = $cell['seatId'] ?? null;

				if ($requestedId !== null) {
					$requestedSeat = Seat::findOne([
						'id' => $requestedId,
						'room_id' => $roomId,
					]);

					if ($requestedSeat === null || self::seatKey($requestedSeat->row, (int) $requestedSeat->number) !== $key) {
						throw new \DomainException("Seat UUID does not match coordinate: {$key}");
					}

					$seat = $requestedSeat;
				}

				if ($seat === null) {
					$seat = new Seat([
						'room_id' => $roomId,
						'row' => self::rowName($cell['row']),
						'number' => $cell['column'],
					]);

					if (!$seat->save()) {
						throw new \DomainException(implode('; ', $seat->getErrorSummary(true)));
					}
				}

				$normalized['cells'][$index]['seatId'] = (string) $seat->getPrimaryKey();
				$desired[$key] = $seat;
			}

			foreach ($existing as $key => $seat) {
				if (isset($desired[$key])) {
					continue;
				}

				if (self::hasProtectedReference((string) $seat->getPrimaryKey())) {
					throw new \DomainException(
						"Cannot remove seat {$key}; it has tickets or active holds.",
					);
				}

				SeatHold::deleteAll([
					'and',
					['seat_id' => $seat->getPrimaryKey()],
					['<=', 'expires_at', new Expression('CURRENT_TIMESTAMP')],
				]);

				if ($seat->delete() === false) {
					throw new \DomainException("Unable to remove seat {$key}.");
				}
			}

			$room->layout = new JsonExpression($normalized, JsonExpression::TYPE_JSONB);

			if (!$room->save(false, ['layout'])) {
				throw new \DomainException('Unable to save room layout.');
			}

			$room->layout = $normalized;

			return $normalized;
		});
	}

	/**
	 * @return array<string, mixed>
	 */
	private static function normalize(array $layout): array
	{
		$grid = $layout['grid'] ?? [];
		$rows = self::positiveInt($grid['rows'] ?? null, 'grid.rows');
		$columns = self::positiveInt($grid['columns'] ?? null, 'grid.columns');

		$normalized = [
			'version' => 1,
			'grid' => [
				'rows' => $rows,
				'columns' => $columns,
			],
			'screen' => self::normalizeScreen($layout['screen'] ?? [], $columns),
			'entrances' => self::normalizeMarkers($layout['entrances'] ?? [], 'entrance'),
			'exits' => self::normalizeMarkers($layout['exits'] ?? [], 'exit'),
			'cells' => [],
		];

		$seenCoordinates = [];
		foreach ($layout['cells'] ?? [] as $cell) {
			if (!is_array($cell)) {
				throw new \DomainException('Each room layout cell must be an object.');
			}

			$row = self::positiveInt($cell['row'] ?? null, 'cell.row');
			$column = self::positiveInt($cell['column'] ?? null, 'cell.column');

			if ($row > $rows || $column > $columns) {
				throw new \DomainException('A room layout cell is outside the configured grid.');
			}

			$type = (string) ($cell['type'] ?? 'empty');

			if (!in_array($type, ['seat', 'aisle', 'empty'], true)) {
				throw new \DomainException("Unsupported room layout cell type: {$type}");
			}

			$coordinate = "{$row}:{$column}";

			if (isset($seenCoordinates[$coordinate])) {
				throw new \DomainException("Duplicate room layout cell: {$coordinate}");
			}
			$seenCoordinates[$coordinate] = true;

			$normalizedCell = [
				'row' => $row,
				'column' => $column,
				'type' => $type,
			];

			if ($type === 'seat' && isset($cell['seatId']) && (string) $cell['seatId'] !== '') {
				if (!self::isUuid((string) $cell['seatId'])) {
					throw new \DomainException('Seat UUID is invalid.');
				}

				$normalizedCell['seatId'] = (string) $cell['seatId'];
			}

			$normalized['cells'][] = $normalizedCell;
		}

		return $normalized;
	}

	/**
	 * @return array<string, mixed>
	 */
	private static function normalizeScreen(array $screen, int $columns): array
	{
		return [
			'label' => trim((string) ($screen['label'] ?? 'Ecrã')) ?: 'Ecrã',
			'row' => self::nonNegativeInt($screen['row'] ?? 0, 'screen.row'),
			'column' => self::nonNegativeInt($screen['column'] ?? 0, 'screen.column'),
			'rowSpan' => self::positiveInt($screen['rowSpan'] ?? 1, 'screen.rowSpan'),
			'columnSpan' => self::positiveInt($screen['columnSpan'] ?? $columns, 'screen.columnSpan'),
		];
	}

	/**
	 * @return list<array{id: string, label: string, row: int, column: int}>
	 */
	private static function normalizeMarkers(mixed $markers, string $prefix): array
	{
		if (!is_array($markers)) {
			throw new \DomainException("{$prefix} markers must be an array.");
		}

		$result = [];
		foreach (array_values($markers) as $index => $marker) {
			if (!is_array($marker)) {
				throw new \DomainException("Each {$prefix} marker must be an object.");
			}

			$result[] = [
				'id' => trim((string) ($marker['id'] ?? "{$prefix}-".($index + 1))) ?: "{$prefix}-".($index + 1),
				'label' => trim((string) ($marker['label'] ?? ucfirst($prefix))) ?: ucfirst($prefix),
				'row' => self::nonNegativeInt($marker['row'] ?? 0, "{$prefix}.row"),
				'column' => self::nonNegativeInt($marker['column'] ?? 0, "{$prefix}.column"),
			];
		}

		return $result;
	}

	private static function hasProtectedReference(string $seatId): bool
	{
		return Ticket::find()->where(['seat_id' => $seatId])->exists()
			|| SeatHold::find()
				->where(['seat_id' => $seatId])
				->andWhere(['>', 'expires_at', new Expression('CURRENT_TIMESTAMP')])
				->exists();
	}

	private static function seatKeyFromCell(array $cell): string
	{
		return self::seatKey(self::rowName($cell['row']), (int) $cell['column']);
	}

	private static function seatKey(string $row, int $number): string
	{
		return strtoupper($row).':'.$number;
	}

	private static function rowName(int $row): string
	{
		return $row <= 26 ? chr(64 + $row) : 'R'.$row;
	}

	private static function positiveInt(mixed $value, string $field): int
	{
		$result = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

		if ($result === false) {
			throw new \DomainException("{$field} must be a positive integer.");
		}

		return (int) $result;
	}

	private static function nonNegativeInt(mixed $value, string $field): int
	{
		$result = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);

		if ($result === false) {
			throw new \DomainException("{$field} must be a non-negative integer.");
		}

		return (int) $result;
	}

	private static function isUuid(string $value): bool
	{
		return preg_match(
			'/\A[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}\z/i',
			$value,
		) === 1;
	}
}
