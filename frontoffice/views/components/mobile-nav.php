<?php

use common\widgets\Icon;
use yii\helpers\Html;

/** @var yii\web\View $this */

$controller = Yii::$app->controller->id;
?>

<nav class="mobile-nav" aria-label="Navegação móvel">
	<?php foreach ([
		['Início', 'house', '/home/index', ['home']],
		['Pesquisa', 'search', '/movie/index', ['movie', 'session']],
		['Bilhetes', 'ticket', '/ticket/index', ['ticket', 'checkout']],
		['Conta', 'circle-user-round', '/account/index', ['account']],
	] as [$label, $icon, $url, $controllers]): ?>

	<?= Html::a(
		Icon::widget(
			[
				'name' => $icon,
			],
		).'<span>'.Html::encode($label).'</span>',
		[$url],
		[
			'class' => in_array($controller, $controllers, true) ? 'is-active' : '',
			'aria-current' => in_array($controller, $controllers, true) ? 'page' : null,
		],
	) ?>

	<?php endforeach ?>
</nav>
