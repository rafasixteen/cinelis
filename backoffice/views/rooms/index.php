<?php

use common\widgets\Badge;
use common\widgets\Button;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var array $rooms */
?>

<main id="main-content" class="page">
	<?= $this->render('../components/page-heading', [
		'title' => 'Salas',
		'description' => 'Configure a capacidade e a disposição de cada sala.',
		'action' => [
			'label' => 'Adicionar sala',
			'url' => ['/rooms/create'],
		],
	]) ?>
	<div class="rooms">
		<?php foreach ($rooms as $room): ?>
			<article class="panel room">
				<span class="room-number"><?= sprintf('%02d', $room['id']) ?>
				</span>
				<div>
					<h2><?= Html::encode($room['name']) ?>
					</h2>
					<p><?= $room['capacity'] ?>
lugares</p>
				</div>
				<?= Badge::widget([
					'label' => $room['status'],
					'variant' => 'success',
				]) ?>

				<?= Button::widget([
					'label' => 'Editar layout',
					'tag' => 'a',
					'url' => [
						'/rooms/layout',
						'id' => $room['id'],
					],
					'variant' => 'secondary',
					'options' => ['aria-label' => 'Editar layout da '.$room['name']],
				]) ?>
			</article>

		<?php endforeach ?>
	</div>
</main>
