<?php

namespace backoffice\data;

use yii\web\NotFoundHttpException;

/** Fixed demonstration records. This catalog never reads or writes a database. */
final class Operations
{
	public const DATE = '2026-10-27';

	public static function movies(): array
	{
		return [
			[
				'id' => 1,
				'title' => 'A última luz',
				'duration' => 120,
				'rating' => 'M/12',
				'premiere' => '2026-10-10',
				'status' => 'Em cartaz',
				'genre' => 'Drama',
				'synopsis' => 'Uma viagem de regresso a Lisboa muda a forma como Leonor vê a sua cidade. Entre memórias e encontros inesperados, descobre que ainda há histórias por contar.',
			],
			[
				'id' => 2,
				'title' => 'Entre marés',
				'duration' => 108,
				'rating' => 'M/12',
				'premiere' => '2026-10-15',
				'status' => 'Em cartaz',
				'genre' => 'Romance',
				'synopsis' => 'Numa pequena vila junto ao mar, dois caminhos voltam a cruzar-se depois de muitos anos.',
			],
			[
				'id' => 3,
				'title' => 'Órbita',
				'duration' => 132,
				'rating' => 'M/12',
				'premiere' => '2026-10-17',
				'status' => 'Em cartaz',
				'genre' => 'Ficção científica',
				'synopsis' => 'Uma tripulação recebe um sinal que pode mudar tudo o que sabemos sobre o universo.',
			],
			[
				'id' => 4,
				'title' => 'Dias de vento',
				'duration' => 96,
				'rating' => 'M/6',
				'premiere' => '2026-10-22',
				'status' => 'Em cartaz',
				'genre' => 'Aventura',
				'synopsis' => 'Três amigos partem numa viagem pela costa portuguesa, guiados pelo vento e pela curiosidade.',
			],
			[
				'id' => 5,
				'title' => 'O outro lado',
				'duration' => 115,
				'rating' => 'M/16',
				'premiere' => '2026-11-05',
				'status' => 'Em breve',
				'genre' => 'Suspense',
				'synopsis' => 'Uma porta esquecida revela um segredo que atravessa gerações.',
			],
		];
	}

	public static function rooms(): array
	{
		return [
			[
				'id' => 1,
				'name' => 'Sala 1',
				'capacity' => 60,
				'rows' => 6,
				'columns' => 10,
				'status' => 'Ativa',
			],
			[
				'id' => 2,
				'name' => 'Sala 2',
				'capacity' => 80,
				'rows' => 8,
				'columns' => 10,
				'status' => 'Ativa',
			],
			[
				'id' => 3,
				'name' => 'Sala 3',
				'capacity' => 100,
				'rows' => 10,
				'columns' => 10,
				'status' => 'Ativa',
			],
		];
	}

	public static function sessions(): array
	{
		$times = [
			'18:30',
			'19:30',
			'20:30',
			'21:30',
			'22:30',
		];
		$sold = [
			27,
			66,
			42,
			60,
			18,
		];
		$result = [];
		foreach (self::movies() as $index => $movie) {
			$room = self::rooms()[$index % 2];
			$result[] = [
				'id' => $movie['id'],
				'movieId' => $movie['id'],
				'movie' => $movie['title'],
				'roomId' => $room['id'],
				'room' => $room['name'],
				'capacity' => $room['capacity'],
				'date' => self::DATE,
				'time' => $times[$index],
				'price' => 7.50,
				'sold' => $sold[$index],
				'occupancy' => (int) round($sold[$index] / $room['capacity'] * 100),
			];
		}

		return $result;
	}

	public static function users(): array
	{
		return [
			[
				'id' => 1,
				'name' => 'Ana Silva',
				'email' => 'ana@cinelis.pt',
				'role' => 'Admin',
				'status' => 'Ativo',
			],
			[
				'id' => 2,
				'name' => 'João Costa',
				'email' => 'joao@cinelis.pt',
				'role' => 'Funcionário',
				'status' => 'Ativo',
			],
			[
				'id' => 3,
				'name' => 'Maria Santos',
				'email' => 'maria@cinelis.pt',
				'role' => 'Funcionário',
				'status' => 'Ativo',
			],
		];
	}

	public static function tickets(array $session): array
	{
		$names = [
			'Sofia Almeida',
			'Miguel Ferreira',
			'Inês Martins',
			'Pedro Costa',
			'Leonor Silva',
			'Tiago Sousa',
		];
		$records = [];
		for ($index = 0; $index < $session['sold']; $index++) {
			$records[] = [
				'id' => $session['id'] * 1000 + $index,
				'seat' => chr(65 + intdiv($index, 10)).($index % 10 + 1),
				'customer' => $names[$index % count($names)],
				'code' => 'CIN-'.($session['id'] * 1000 + $index),
				'price' => $session['price'],
				'used' => $index % 5 < 2,
			];
		}

		return $records;
	}

	public static function orders(): array
	{
		$result = [];
		foreach (array_slice(self::tickets(self::sessions()[2]), 0, 12) as $index => $ticket) {
			$session = self::sessions()[$index % 5];
			$result[] = [
				'id' => 1001 + $index,
				'customer' => $ticket['customer'],
				'movie' => $session['movie'],
				'sessionId' => $session['id'],
				'date' => self::DATE,
				'quantity' => 2,
				'total' => 15.00,
				'status' => $index === 5 ? 'Cancelada' : 'Paga',
			];
		}

		return $result;
	}

	public static function find(string $collection, int $id): array
	{
		foreach (self::$collection() as $record) {
			if ($record['id'] === $id) {
				return $record;
			}
		}

		throw new NotFoundHttpException('Este registo de demonstração não existe.');
	}

	public static function money(float $value): string
	{
		return number_format($value, 2, ',', '.').' €';
	}
}
