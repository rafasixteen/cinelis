<?php

use common\widgets\Badge;
use common\widgets\Button;
use common\widgets\Card;
use common\widgets\Combobox;
use common\widgets\Icon;
use common\widgets\Input;
use common\widgets\Select;
use yii\grid\GridView;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var yii\data\ArrayDataProvider $sessions */

$this->title = 'Overview · Cinelis Backoffice';

$metrics = [
	[
		'class' => 'revenue',
		'label' => 'Revenue',
		'value' => '€4,286',
		'trend' => '↗ 12.4%',
		'trendContext' => 'from yesterday',
		'variant' => 'warning',
		'icon' => 'circle-dollar-sign',
	],
	[
		'class' => 'tickets',
		'label' => 'Tickets sold',
		'value' => '318',
		'trend' => '↗ 8.1%',
		'trendContext' => 'from yesterday',
		'variant' => 'info',
		'icon' => 'ticket',
	],
	[
		'class' => 'occupancy',
		'label' => 'Avg. occupancy',
		'value' => '72%',
		'trend' => '↗ 4.6%',
		'trendContext' => 'this week',
		'variant' => 'success',
		'icon' => 'users-round',
	],
	[
		'class' => 'sessions',
		'label' => 'Sessions',
		'value' => '24',
		'trend' => '6 remaining',
		'trendContext' => 'today',
		'variant' => 'default',
		'icon' => 'calendar-days',
	],
];

$roomStatuses = [
	[
		'number' => '01',
		'title' => 'In session',
		'detail' => 'Dune: Part Two · ends 17:52',
		'badge' => ['label' => 'Live', 'variant' => 'danger'],
	],
	[
		'number' => '02',
		'title' => 'Ready',
		'detail' => 'Next session at 20:15',
		'badge' => ['label' => 'Ready', 'variant' => 'success'],
	],
	[
		'number' => '03',
		'title' => 'Cleaning',
		'detail' => 'Ready in ~12 min',
		'badge' => ['label' => 'Soon', 'variant' => 'warning'],
	],
	[
		'number' => '04',
		'title' => 'Ready',
		'detail' => 'Next session at 21:00',
		'badge' => ['label' => 'Ready', 'variant' => 'success'],
	],
];

$activities = [
	[
		'icon' => 'T',
		'variant' => 'success',
		'message' => '<strong>12 tickets</strong> sold for Dune: Part Two',
		'detail' => 'Online order · 4 minutes ago',
		'value' => '€142.80',
		'valueTag' => 'strong',
	],
	[
		'icon' => 'S',
		'variant' => 'info',
		'message' => 'Session time updated for <strong>Past Lives</strong>',
		'detail' => 'Changed by Miguel Costa · 18 minutes ago',
		'value' => '19:00',
		'valueClass' => 'muted',
	],
	[
		'icon' => 'M',
		'variant' => 'primary',
		'message' => '<strong>Perfect Days</strong> added to the catalogue',
		'detail' => 'Added by Sofia Martins · 42 minutes ago',
		'value' => 'New',
		'valueClass' => 'muted',
	],
];
?>

<main class="dashboard">
	<section class="welcome">
		<div>
			<p class="eyebrow">Friday, 2 October</p>
			<h1>Good morning, Admin</h1>
			<p class="welcome-copy">Here’s what is happening at Cinelis Lisboa today.</p>
		</div>

		<?= Button::widget([
			'label' => 'Add session',
			'icon' => 'plus',
			'variant' => 'secondary',
			'options' => ['disabled' => true],
		]) ?>
	</section>

	<section class="metrics-section" aria-labelledby="metrics-title">
		<div class="section-heading">
			<div>
				<p class="eyebrow">Live operations</p>
				<h2 id="metrics-title">Today at a glance</h2>
			</div>

			<?= Badge::widget([
				'label' => 'Updated just now',
				'variant' => 'success',
				'options' => ['class' => 'live-indicator'],
			]) ?>
		</div>

		<div class="metrics-grid">
			<?php foreach ($metrics as $metric): ?>
				<?php Card::begin([
					'tag' => 'article',
					'options' => ['class' => 'metric-card ' . $metric['class']],
				]) ?>
					<div class="metric-icon metric-icon--<?= Html::encode($metric['variant']) ?>">
						<?= Icon::widget(['name' => $metric['icon']]) ?>
					</div>
					<p class="metric-label"><?= Html::encode($metric['label']) ?></p>
					<p class="metric-value"><?= Html::encode($metric['value']) ?></p>
					<p class="metric-trend metric-trend--<?= Html::encode($metric['variant']) ?>">
						<?= Html::encode($metric['trend']) ?>
						<span><?= Html::encode($metric['trendContext']) ?></span>
					</p>
				<?php Card::end() ?>
			<?php endforeach ?>
		</div>
	</section>

	<div class="dashboard-grid">
		<?php Card::begin(['tag' => 'section', 'options' => ['class' => 'panel sessions-panel']]) ?>
			<header class="panel-header">
				<div>
					<p class="eyebrow">Schedule</p>
					<h2 id="sessions-title">Today’s sessions</h2>
				</div>

				<?= Button::widget([
					'label' => 'View schedule →',
					'tag' => 'a',
					'url' => '#',
					'variant' => 'ghost',
					'options' => ['aria-disabled' => 'true', 'class' => 'text-action'],
				]) ?>
			</header>

			<div class="dashboard-filters" aria-label="Session filters">
				<?= Input::widget([
					'name' => 'session-search',
					'label' => 'Search',
					'options' => ['placeholder' => 'Search sessions'],
				]) ?>
				<?= Select::widget([
					'name' => 'session-status',
					'label' => 'Status',
					'items' => [
						'all' => 'All statuses',
						'upcoming' => 'Upcoming',
						'selling' => 'Selling',
					],
					'value' => 'all',
				]) ?>
				<?= Combobox::widget([
					'name' => 'session-movie',
					'label' => 'Movie',
					'items' => [
						'all' => 'All movies',
						'dune' => 'Dune: Part Two',
						'past-lives' => 'Past Lives',
					],
					'value' => 'all',
				]) ?>
				<?= Button::widget([
					'label' => 'Apply filters',
					'variant' => 'secondary',
					'options' => ['type' => 'button'],
				]) ?>
			</div>

			<div class="table-scroll">
				<?= GridView::widget([
					'dataProvider' => $sessions,
					'layout' => '{items}',
					'options' => ['class' => 'grid-view dashboard-grid-view'],
					'tableOptions' => ['class' => 'dashboard-table'],
					'columns' => [
						[
							'attribute' => 'movie',
							'label' => 'Movie',
							'format' => 'raw',
							'value' => static fn(array $model): string => Html::tag(
								'span',
								'',
								['class' => 'movie-accent movie-accent--' . $model['accent']],
							) . Html::tag('strong', Html::encode($model['movie'])),
						],
						'room',
						'time',
						[
							'attribute' => 'occupancy',
							'label' => 'Occupancy',
							'format' => 'raw',
							'value' => static fn(array $model): string => Html::tag(
								'span',
								Html::tag('span', '', ['style' => 'width: ' . (int) $model['occupancy'] . '%']),
								['class' => 'occupancy-bar'],
							) . Html::encode($model['occupancy']) . '%',
						],
						[
							'attribute' => 'status',
							'label' => 'Status',
							'format' => 'raw',
							'value' => static fn(array $model): string => Badge::widget([
								'label' => $model['status'],
								'variant' => $model['statusVariant'],
							]),
						],
					],
				]) ?>
			</div>
		<?php Card::end() ?>

		<?php Card::begin(['tag' => 'aside', 'options' => ['class' => 'panel rooms-panel']]) ?>
			<header class="panel-header">
				<div>
					<p class="eyebrow">Venue</p>
					<h2 id="rooms-title">Room status</h2>
				</div>
			</header>

			<ul class="room-list">
				<?php foreach ($roomStatuses as $room): ?>
					<li>
						<span class="room-number"><?= Html::encode($room['number']) ?></span>
						<div>
							<strong><?= Html::encode($room['title']) ?></strong>
							<span><?= Html::encode($room['detail']) ?></span>
						</div>
						<?= Badge::widget($room['badge']) ?>
					</li>
				<?php endforeach ?>
			</ul>
		<?php Card::end() ?>
	</div>

	<?php Card::begin(['tag' => 'section', 'options' => ['class' => 'panel activity-panel']]) ?>
		<header class="panel-header">
			<div>
				<p class="eyebrow">Latest updates</p>
				<h2 id="activity-title">Recent activity</h2>
			</div>

			<?= Button::widget([
				'label' => 'View all →',
				'tag' => 'a',
				'url' => '#',
				'variant' => 'ghost',
				'options' => ['aria-disabled' => 'true', 'class' => 'text-action'],
			]) ?>
		</header>

		<div class="activity-list">
	<?php foreach ($activities as $activity): ?>
		<?php $valueTag = $activity['valueTag'] ?? 'span'; ?>
				<article>
					<div class="activity-icon activity-icon--<?= Html::encode($activity['variant']) ?>">
						<?= Html::encode($activity['icon']) ?>
					</div>
					<div>
						<p><?= $activity['message'] ?></p>
						<span><?= Html::encode($activity['detail']) ?></span>
					</div>
					<<?= $valueTag ?>
						class="activity-value<?= isset($activity['valueClass']) ? ' ' . Html::encode($activity['valueClass']) : '' ?>"
					>
						<?= Html::encode($activity['value']) ?>
					</<?= $valueTag ?>>
				</article>
			<?php endforeach ?>
		</div>
	<?php Card::end() ?>
</main>
