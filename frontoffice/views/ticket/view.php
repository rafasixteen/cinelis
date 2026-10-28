<?php

use common\widgets\Button;
use frontoffice\data\Catalog;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var array $ticket */
/** @var bool $confirmation */

$this->title = $confirmation ? 'Exemplo de confirmação' : 'O seu bilhete';
?>

<header class="page-heading">
	<span class="ui-badge ui-badge--success">Pré-visualização · demonstração</span>
	<h1><?= $confirmation ? 'O cinema espera por si.' : 'O seu bilhete' ?>
	</h1>
	<p>Exemplo de bilhete. Não foi efetuada uma compra e este bilhete não permite a entrada.</p>
</header>
<article class="panel ticket-detail">
	<div>
		<h2><?= Html::encode($ticket['movie']['title']) ?>
		</h2>
		<p class="muted"><?= Catalog::date($ticket['date']) ?>
· <?= $ticket['time'] ?>
· <?= $ticket['room'] ?>
		</p>
		<span class="ui-badge">LUGAR <?= Html::encode($ticket['seat']) ?>
		</span>
		<p class="ticket-code"><?= Html::encode($ticket['id']) ?>
-<?= Html::encode($ticket['seat']) ?>
		</p>
		<p class="note">Bilhete ilustrativo · sem validade</p>
	</div>
	<div class="qr-wrap">
		<div class="qr" role="img" aria-label="Código ilustrativo sem validade">
			<svg viewBox="0 0 29 29" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
				<rect width="29" height="29" fill="white"/>
				<?php for ($y = 2; $y < 27; $y++): ?>

					<?php for ($x = 2; $x < 27; $x++): ?>

						<?php
						$finder = ($x < 9 && $y < 9) || ($x > 19 && $y < 9) || ($x < 9 && $y > 19);
						?>

						<?php if (!$finder && (($x * 17 + $y * 13 + $x * $y) % 7 < 3)): ?>
							<rect x="<?= $x ?>
" y="<?= $y ?>
" width="1" height="1" fill="black"/>
						<?php endif ?>

						<?php endfor ?>

					<?php endfor ?>

					<?php foreach ([
						[2, 2],
						[20, 2],
						[2, 20],
					] as [$x, $y]): ?>
					<rect x="<?= $x ?>
" y="<?= $y ?>
" width="7" height="7" fill="black"/>
					<rect x="<?= $x + 1 ?>
" y="<?= $y + 1 ?>
" width="5" height="5" fill="white"/>
					<rect x="<?= $x + 2 ?>
" y="<?= $y + 2 ?>
" width="3" height="3" fill="black"/>
				<?php endforeach ?>
							</svg>
		</div>
		<p class="note">Código ilustrativo · sem validade</p>
	</div>
				</article>
				<div class="ticket-actions">
	<?= Button::widget([
		'label' => 'Descarregar bilhetes',
		'options' => ['data-demo-action' => true],
	]) ?>

					<?= Button::widget([
						'tag' => 'a',
						'label' => 'Os meus bilhetes',
						'url' => ['/ticket/index'],
						'variant' => 'secondary',
					]) ?>
				</div>
