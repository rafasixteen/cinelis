<?php

/** @var yii\web\View $this */

$this->title = 'Cinelis';
?>

<main class="home">
	<section class="hero">
		<div class="backdrop"></div>

		<div class="content">
			<p class="eyebrow">Now showing</p>

			<h1 class="title">
				Experience cinema<br>
				the way it should be.
			</h1>

			<p class="description">
				Discover what is showing at Cinelis, choose your session
				and reserve your seats.
			</p>

			<div class="actions">
				<a href="/movies">See movies</a>
			</div>
		</div>
	</section>

	<section class="section">
		<header class="header">
			<div>
				<p class="eyebrow">At Cinelis</p>
				<h2 class="title">Now showing</h2>
			</div>
		</header>

		<div class="movies">
			<article class="movie">
				<div class="poster">
					<div class="placeholder">Poster</div>
				</div>

				<div class="body">
					<div>
						<h3 class="title">Movie title</h3>
						<p class="meta">Drama · 2h 14m</p>
					</div>

					<a href="#" class="action">Sessions</a>
				</div>
			</article>
		</div>
	</section>
</main>