<?php

declare(strict_types=1);

namespace backoffice\controllers;

use common\models\Order;
use common\models\Session;
use common\models\Ticket;
use common\rbac\RbacDefinitions;
use DateTimeImmutable;
use DateTimeZone;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;

final class HomeController extends Controller
{
	private const TIMEZONE = 'Europe/Lisbon';

	public function behaviors(): array
	{
		return [
			'access' => [
				'class' => AccessControl::class,
				'rules' => [
					[
						'allow' => true,
						'roles' => [RbacDefinitions::PERMISSION_BACK_OFFICE_DASHBOARD_VIEW],
					],
				],
			],
			'verbs' => [
				'class' => VerbFilter::class,
				'actions' => ['index' => ['GET']],
			],
		];
	}

	public function actionIndex(): string
	{
		$todayRange = self::dateRange();
		$occupancyRange = self::dateRange(Yii::$app->request->get('date'));
		$sessions = self::sessionsForRange($occupancyRange['start'], $occupancyRange['end']);
		$todaySessions = self::sessionsForRange($todayRange['start'], $todayRange['end']);
		$totalCapacity = 0;
		$totalSold = 0;
		foreach ($todaySessions as $session) {
			$totalCapacity += count($session->room?->seats ?? []);
			$totalSold += count($session->tickets);
		}

		return $this->render('index', [
			'sessions' => $sessions,
			'todayDate' => $todayRange['date'],
			'occupancyDate' => $occupancyRange['date'],
			'occupancySessionCount' => count($sessions),
			'occupancyCapacity' => array_sum(array_map(
				static fn (Session $session): int => count($session->room?->seats ?? []),
				$sessions,
			)),
			'occupancySold' => array_sum(array_map(
				static fn (Session $session): int => count($session->tickets),
				$sessions,
			)),
			'orders' => Order::find()
				->with(['tickets.session.movie'])
				->where(['>=', 'created_at', $todayRange['start']])
				->andWhere(['<', 'created_at', $todayRange['end']])
				->orderBy(['created_at' => SORT_DESC])
				->limit(3)
				->all(),
			'sales' => (float) (Order::find()
				->where(['>=', 'created_at', $todayRange['start']])
				->andWhere(['<', 'created_at', $todayRange['end']])
				->sum('total') ?? 0),
			'ticketCount' => (int) Ticket::find()
				->where(['>=', 'created_at', $todayRange['start']])
				->andWhere(['<', 'created_at', $todayRange['end']])
				->count(),
			'occupancy' => $totalCapacity > 0 ? (int) round($totalSold / $totalCapacity * 100) : 0,
		]);
	}

	/**
	 * @return array{date: string, start: string, end: string}
	 */
	public static function dateRange(?string $value = null, ?DateTimeImmutable $today = null): array
	{
		$timezone = new DateTimeZone(self::TIMEZONE);
		$today ??= new DateTimeImmutable('today', $timezone);
		$today = $today->setTimezone($timezone)->setTime(0, 0);
		$date = trim((string) $value);

		if ($date !== '') {
			$selected = DateTimeImmutable::createFromFormat('!Y-m-d', $date, $timezone);
			$errors = DateTimeImmutable::getLastErrors();

			if ($selected !== false && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))) {
				$today = $selected;
			}
		}

		return [
			'date' => $today->format('Y-m-d'),
			'start' => $today->format('Y-m-d H:i:s'),
			'end' => $today->modify('+1 day')->format('Y-m-d H:i:s'),
		];
	}

	/**
	 * @return list<Session>
	 */
	private static function sessionsForRange(string $start, string $end): array
	{
		return Session::find()
			->with(['movie', 'room.seats', 'tickets'])
			->where(['status' => Session::STATUS_SCHEDULED])
			->andWhere(['>=', 'starts_at', $start])
			->andWhere(['<', 'starts_at', $end])
			->orderBy(['starts_at' => SORT_ASC])
			->all();
	}
}
