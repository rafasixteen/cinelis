<?php

use common\widgets\Button;

/** @var string $cancel */
/** @var string|null $label */
?>

<div class="form-actions">
	<?= Button::widget([
		'label' => 'Cancelar',
		'tag' => 'a',
		'url' => $cancel,
		'variant' => 'secondary',
	]) ?>

	<?= Button::widget([
		'label' => $label ?? 'Guardar alterações',
		'options' => ['type' => 'submit'],
	]) ?>
</div>
