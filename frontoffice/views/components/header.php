<?php

use common\widgets\Button;
use common\widgets\Icon;
use common\widgets\Logo;
use yii\helpers\Html;

/** @var yii\web\View $this */

$controller = Yii::$app->controller->id;
?>

<header class="site-header">
	<div class="container header-inner">
		<?= Html::a(
			Logo::widget(
				[
					'variant' => 'auto',
				],
			),
			['/home/index'],
			[
				'class' => 'brand',
				'aria-label' => 'CineLis — início',
			],
		) ?>
		<nav class="desktop-nav" aria-label="Navegação principal">
			<?php foreach ([
				['Filmes', '/movie/index', ['home', 'movie']],
				['Sessões', '/session/index', ['session']],
				['Os meus bilhetes', '/ticket/index', ['ticket']],
			] as [$label, $route, $controllers]): ?>

			<?= Html::a(
				$label,
				[$route],
				[
					'class' => in_array($controller, $controllers, true) ? 'is-active' : '',
					'aria-current' => in_array($controller, $controllers, true) ? 'page' : null,
				],
			) ?>

			<?php endforeach ?>
		</nav>
		<div class="header-actions">
			<button
				class="icon-button"
				type="button"
				data-theme-toggle
				aria-label="Alternar tema claro e escuro"
				title="Alternar tema"
			>
				<?= Icon::widget(
					[
						'name' => 'sun-moon',
					],
				) ?>
			</button>
			<span class="language" lang="pt">PT</span>
			<?= Button::widget([
				'tag' => 'a',
				'label' => 'A minha conta',
				'url' => ['/account/index'],
				'variant' => 'secondary',
				'options' => ['class' => 'account-link'],
			]) ?>
		</div>
	</div>
</header>
