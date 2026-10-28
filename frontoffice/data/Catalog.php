<?php

namespace frontoffice\data;

use yii\web\NotFoundHttpException;

/** Stable presentation fixtures; deliberately independent of the database. */
final class Catalog
{
	public static function movies(): array
	{
		return [
			'a-ultima-luz' => [
				'id' => 'a-ultima-luz',
				'title' => 'A última luz',
				'genre' => 'Drama',
				'duration' => 120,
				'rating' => 'M/12',
				'country' => 'Portugal',
				'art' => 'sun',
				'synopsis' => 'Uma viagem de regresso a Lisboa muda a forma como Leonor vê a sua cidade. Entre memórias e reencontros, encontra uma nova maneira de ficar.',
				'director' => 'Mariana Costa',
			],
			'entre-mares' => [
				'id' => 'entre-mares',
				'title' => 'Entre marés',
				'genre' => 'Drama',
				'duration' => 108,
				'rating' => 'M/12',
				'country' => 'Portugal',
				'art' => 'waves',
				'synopsis' => 'Numa pequena vila junto ao Atlântico, dois irmãos voltam a encontrar-se. Uma história sobre as raízes que nos prendem e os caminhos que escolhemos.',
				'director' => 'João Mendes',
			],
			'orbita' => [
				'id' => 'orbita',
				'title' => 'Órbita',
				'genre' => 'Ficção científica',
				'duration' => 126,
				'rating' => 'M/12',
				'country' => 'Portugal',
				'art' => 'orbit',
				'synopsis' => 'Uma astronauta recebe uma mensagem inesperada durante a sua última missão. A milhares de quilómetros de casa, terá de decidir o que significa regressar.',
				'director' => 'Inês Almeida',
			],
			'dias-de-verao' => [
				'id' => 'dias-de-verao',
				'title' => 'Dias de verão',
				'genre' => 'Comédia',
				'duration' => 96,
				'rating' => 'M/6',
				'country' => 'Portugal',
				'art' => 'sun',
				'synopsis' => 'Quatro amigos, uma casa de férias e um verão cheio de planos que não correm como esperado. Uma celebração da amizade e dos pequenos recomeços.',
				'director' => 'Rita Gomes',
			],
			'o-outro-lado' => [
				'id' => 'o-outro-lado',
				'title' => 'O outro lado',
				'genre' => 'Thriller',
				'duration' => 114,
				'rating' => 'M/16',
				'country' => 'Portugal',
				'art' => 'waves',
				'synopsis' => 'Ao investigar o desaparecimento de um vizinho, uma jornalista descobre que a verdade pode estar mais perto do que imagina.',
				'director' => 'Pedro Martins',
			],
		];
	}

	public static function movie(string $id): array
	{
		return self::movies()[$id] ?? throw new NotFoundHttpException('Filme não encontrado.');
	}

	public static function days(): array
	{
		return [
			'2026-10-27' => 'Hoje · 27 out.',
			'2026-10-28' => 'Amanhã',
			'2026-10-29' => '29 out.',
		];
	}

	public static function day(string $day): string
	{
		return array_key_exists($day, self::days()) ? $day : '2026-10-27';
	}

	public static function sessions(?string $movie = null, ?string $date = null): array
	{
		$sessions = [];
		foreach (self::movies() as $film) {
			foreach (array_keys(self::days()) as $day) {
				foreach ([
					'18:00',
					'20:30',
					'21:30',
				] as $index => $time) {
					$id = $film['id'].'-'.str_replace('-', '', $day).'-'.str_replace(':', '', $time);
					$sessions[$id] = [
						'id' => $id,
						'movie' => $film,
						'date' => $day,
						'time' => $time,
						'room' => $index === 2 ? 'Sala 2' : 'Sala 1',
						'price' => 7.5,
					];
				}
			}
		}

		return array_filter(
			$sessions,
			static fn (array $session): bool => (
				($movie === null || $session['movie']['id'] === $movie)
				&& ($date === null || $session['date'] === $date)
			),
		);
	}

	public static function session(string $id): array
	{
		return self::sessions()[$id] ?? throw new NotFoundHttpException('Sessão não encontrada.');
	}

	public static function defaultSession(): string
	{
		return 'a-ultima-luz-20261027-2030';
	}

	public static function occupiedSeats(): array
	{
		return [
			'B3',
			'B4',
			'C6',
			'D5',
			'D6',
			'E1',
			'E9',
		];
	}

	public static function seats(string $seats): array
	{
		return array_values(
			array_filter(
				array_unique(explode(',', $seats)),
				static fn (string $seat): bool => (
					preg_match('/^[A-E](?:[1-9]|10)$/', $seat)
					&& !in_array($seat, self::occupiedSeats(), true)
				),
			),
		);
	}

	public static function tickets(string $period = 'upcoming'): array
	{
		$tickets = [];
		foreach (array_slice(array_values(self::movies()), 0, 3) as $index => $movie) {
			$tickets[] = [
				'id' => 'CIN-DEMO-'.($index + 1),
				'movie' => $movie,
				'seat' => 'A'.($index + 2),
				'date' => $period === 'past' ? '2026-10-0'.($index + 1) : '2026-10-'.(27 + $index),
				'time' => '20:30',
				'room' => 'Sala 1',
			];
		}

		return $tickets;
	}

	public static function ticket(string $id): array
	{
		foreach (self::tickets() as $ticket) {
			if ($ticket['id'] === $id) {
				return $ticket;
			}
		}

		throw new NotFoundHttpException('Bilhete de demonstração não encontrado.');
	}

	public static function money(float $value): string
	{
		return number_format($value, 2, ',', ' ').' €';
	}

	public static function date(string $value): string
	{
		return date('d', strtotime($value)).' out. 2026';
	}
}
