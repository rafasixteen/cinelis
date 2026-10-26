<?php

use yii\helpers\Html;

/** @var yii\web\View $this */

$this->title = 'Overview · Cinelis Backoffice';
?>

<main class="dashboard">
	<section class="welcome">
		<div>
			<p class="eyebrow">Friday, 2 October</p>
			<h1>Good morning, Admin</h1>
			<p class="welcome-copy">Here’s what is happening at Cinelis Lisboa today.</p>
		</div>

		<?= Html::tag(
			'span',
			'<span aria-hidden="true">+</span> Add session',
			['class' => 'primary-action disabled', 'aria-disabled' => 'true'],
		) ?>
	</section>

	<section class="metrics-section" aria-labelledby="metrics-title">
		<div class="section-heading">
			<div>
				<p class="eyebrow">Live operations</p>
				<h2 id="metrics-title">Today at a glance</h2>
			</div>

			<span class="live-indicator"><span></span> Updated just now</span>
		</div>

		<div class="metrics-grid">
			<article class="metric-card revenue">
				<div class="metric-icon">
					<svg viewBox="0 0 24 24" aria-hidden="true">
						<path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm3.5 7H11a1 1 0 0 0 0 2h2a3 3 0 1 1 0 6v2h-2v-2H8.5v-2H13a1 1 0 1 0 0-2h-2a3 3 0 0 1 0-6V5h2v2h2.5v2Z" />
					</svg>
				</div>
				<p class="metric-label">Revenue</p>
				<p class="metric-value">€4,286</p>
				<p class="metric-trend positive">↗ 12.4% <span>from yesterday</span></p>
			</article>

			<article class="metric-card tickets">
				<div class="metric-icon">
					<svg viewBox="0 0 24 24" aria-hidden="true">
						<path d="M21 12a3 3 0 0 0-2-2.8V5H5v4.2A3 3 0 0 0 5 15v4h14v-4a3 3 0 0 0 2-3Zm-4 5H7v-4H5a1 1 0 0 1 0-2h2V7h10v4h2a1 1 0 0 1 0 2h-2v4Z" />
					</svg>
				</div>
				<p class="metric-label">Tickets sold</p>
				<p class="metric-value">318</p>
				<p class="metric-trend positive">↗ 8.1% <span>from yesterday</span></p>
			</article>

			<article class="metric-card occupancy">
				<div class="metric-icon">
					<svg viewBox="0 0 24 24" aria-hidden="true">
						<path d="M7 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm10 0a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM1 20v-2a5 5 0 0 1 10 0v2H1Zm12 0v-2c0-1.7-.7-3.3-1.8-4.5A5 5 0 0 1 23 18v2H13Z" />
					</svg>
				</div>
				<p class="metric-label">Avg. occupancy</p>
				<p class="metric-value">72%</p>
				<p class="metric-trend positive">↗ 4.6% <span>this week</span></p>
			</article>

			<article class="metric-card sessions">
				<div class="metric-icon">
					<svg viewBox="0 0 24 24" aria-hidden="true">
						<path d="M7 2h2v2h6V2h2v2h1a3 3 0 0 1 3 3v12a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V7a3 3 0 0 1 3-3h1V2Zm12 8H5v9a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-9Z" />
					</svg>
				</div>
				<p class="metric-label">Sessions</p>
				<p class="metric-value">24</p>
				<p class="metric-trend neutral">6 remaining <span>today</span></p>
			</article>
		</div>
	</section>

	<div class="dashboard-grid">
		<section class="panel sessions-panel" aria-labelledby="sessions-title">
			<header class="panel-header">
				<div>
					<p class="eyebrow">Schedule</p>
					<h2 id="sessions-title">Today’s sessions</h2>
				</div>

				<?= Html::tag('span', 'View schedule →', ['class' => 'text-action disabled', 'aria-disabled' => 'true']) ?>
			</header>

			<div class="table-scroll">
				<table>
					<thead>
						<tr>
							<th>Movie</th>
							<th>Room</th>
							<th>Time</th>
							<th>Occupancy</th>
							<th>Status</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><span class="movie-accent dune"></span><strong>Dune: Part Two</strong></td>
							<td>Room 1</td>
							<td>18:30</td>
							<td><span class="occupancy-bar"><span style="width: 86%"></span></span>86%</td>
							<td><span class="status upcoming">Upcoming</span></td>
						</tr>
						<tr>
							<td><span class="movie-accent past"></span><strong>Past Lives</strong></td>
							<td>Room 3</td>
							<td>19:00</td>
							<td><span class="occupancy-bar"><span style="width: 64%"></span></span>64%</td>
							<td><span class="status selling">Selling</span></td>
						</tr>
						<tr>
							<td><span class="movie-accent zone"></span><strong>The Zone of Interest</strong></td>
							<td>Room 2</td>
							<td>20:15</td>
							<td><span class="occupancy-bar"><span style="width: 48%"></span></span>48%</td>
							<td><span class="status selling">Selling</span></td>
						</tr>
						<tr>
							<td><span class="movie-accent poor"></span><strong>Poor Things</strong></td>
							<td>Room 4</td>
							<td>21:00</td>
							<td><span class="occupancy-bar"><span style="width: 91%"></span></span>91%</td>
							<td><span class="status almost-full">Almost full</span></td>
						</tr>
					</tbody>
				</table>
			</div>
		</section>

		<aside class="panel rooms-panel" aria-labelledby="rooms-title">
			<header class="panel-header">
				<div>
					<p class="eyebrow">Venue</p>
					<h2 id="rooms-title">Room status</h2>
				</div>
			</header>

			<ul class="room-list">
				<li><span class="room-number">01</span>
					<div><strong>In session</strong><span>Dune: Part Two · ends 17:52</span></div><span class="room-state live">Live</span>
				</li>
				<li><span class="room-number">02</span>
					<div><strong>Ready</strong><span>Next session at 20:15</span></div><span class="room-state ready">Ready</span>
				</li>
				<li><span class="room-number">03</span>
					<div><strong>Cleaning</strong><span>Ready in ~12 min</span></div><span class="room-state cleaning">Soon</span>
				</li>
				<li><span class="room-number">04</span>
					<div><strong>Ready</strong><span>Next session at 21:00</span></div><span class="room-state ready">Ready</span>
				</li>
			</ul>
		</aside>
	</div>

	<section class="panel activity-panel" aria-labelledby="activity-title">
		<header class="panel-header">
			<div>
				<p class="eyebrow">Latest updates</p>
				<h2 id="activity-title">Recent activity</h2>
			</div>

			<?= Html::tag('span', 'View all →', ['class' => 'text-action disabled', 'aria-disabled' => 'true']) ?>
		</header>

		<div class="activity-list">
			<article>
				<div class="activity-icon ticket">T</div>
				<div>
					<p><strong>12 tickets</strong> sold for Dune: Part Two</p><span>Online order · 4 minutes ago</span>
				</div>
				<strong class="activity-value">€142.80</strong>
			</article>
			<article>
				<div class="activity-icon schedule">S</div>
				<div>
					<p>Session time updated for <strong>Past Lives</strong></p><span>Changed by Miguel Costa · 18 minutes ago</span>
				</div>
				<span class="activity-value muted">19:00</span>
			</article>
			<article>
				<div class="activity-icon movie">M</div>
				<div>
					<p><strong>Perfect Days</strong> added to the catalogue</p><span>Added by Sofia Martins · 42 minutes ago</span>
				</div>
				<span class="activity-value muted">New</span>
			</article>
		</div>
	</section>
</main>