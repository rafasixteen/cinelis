<?php

use common\models\Room;
use common\widgets\Badge;
use common\widgets\Button;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $provider */
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
		<?php foreach ($provider->getModels() as $room): ?>
			<?php /** @var Room $room */ ?>
			<article class="panel room">
				<span class="room-number" aria-hidden="true">⌂</span>
				<div>
					<h2><?= Html::encode($room->name) ?></h2>
					<p><?= count($room->seats) ?> lugares</p>
				</div>
				<?= Badge::widget(['label' => 'Ativa', 'variant' => 'success']) ?>
				<?= Button::widget([
					'label' => 'Editar layout',
					'tag' => 'a',
					'url' => ['/rooms/layout', 'id' => $room->id],
					'variant' => 'secondary',
					'options' => ['aria-label' => 'Editar layout da '.$room->name],
				]) ?>
			</article>
		<?php endforeach ?>

		<?php if ($provider->getCount() === 0): ?>
			<div class="panel empty">
				<strong>Sem salas</strong>
				<p>Adicione a primeira sala para começar a configurar lugares.</p>
			</div>
		<?php endif ?>
	</div>
</main>
