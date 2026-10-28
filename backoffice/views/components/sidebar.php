<?php

use common\widgets\Icon;
use common\widgets\Logo;
use yii\helpers\Html;

/** @var yii\web\View $this */

$controller = Yii::$app->controller->id;
$groups = [
	'Gestão do cinema' => [
		['home', 'layout-dashboard', 'Dashboard', ['/home/index']],
		['movies', 'clapperboard', 'Filmes', ['/movies/index']],
		['rooms', 'armchair', 'Salas', ['/rooms/index']],
		['sessions', 'calendar-days', 'Sessões', ['/sessions/index']],
		['tickets', 'ticket', 'Bilhetes', ['/tickets/index']],
		['orders', 'receipt-text', 'Encomendas', ['/orders/index']],
	],
	'Administração' => [
		['users', 'users-round', 'Utilizadores', ['/users/index']],
		['settings', 'settings', 'Definições', ['/settings/index']],
	],
];
?>

<aside class="sidebar" aria-label="Navegação principal">
	<div class="sidebar-brand">
		<?= Html::a(
			Logo::widget(
				[
					'variant' => 'dark',
				],
			).'<small>BACKOFFICE</small>',
			['/home/index'],
			[
				'class' => 'brand',
				'aria-label' => 'CineLis · Dashboard',
			],
		) ?>
	</div>
	<nav class="sidebar-nav" aria-label="Gestão do cinema">
		<?php foreach ($groups as $label => $items): ?>
			<div class="nav-group">
				<p class="nav-label"><?= Html::encode($label) ?>
				</p>
				<ul class="sidebar-menu">
					<?php foreach ($items as [$id, $icon, $text, $url]): ?>
						<li class="nav-item <?= $controller === $id ? 'active' : '' ?>
">
							<?= Html::a(
								Icon::widget(
									[
										'name' => $icon,
									],
								).'<span>'.Html::encode($text).'</span>',
								$url,
								[
									'class' => 'nav-link',
									'aria-current' => $controller === $id ? 'page' : null,
									'title' => $text,
								],
							) ?>
						</li>

					<?php endforeach ?>
				</ul>
			</div>

		<?php endforeach ?>
	</nav>
	<div class="sidebar-footer">
		<div>
			<strong>CineLis · Lisboa</strong>
			<span>Administração</span>
		</div>
	</div>
</aside>
