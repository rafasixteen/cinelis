<?php

use yii\helpers\Html;

/** @var string $name */
/** @var string $label */
/** @var string|null $type */
/** @var mixed $value */
/** @var array|null $items */
/** @var array|null $options */
/** @var string|null $class */
/** @var string|null $hint */

$id = 'field-'.preg_replace('/[^a-z0-9-]/i', '-', $name);
$options = array_merge([
	'id' => $id,
	'class' => 'input',
], $options ?? []);
?>

<div class="field <?= Html::encode($class ?? '') ?>
">
	<?= Html::label($label, $id) ?>

	<?php if (isset($items)): ?>

		<?= Html::dropDownList($name, $value ?? '', $items, $options) ?>

	<?php elseif (($type ?? '') === 'textarea'): ?>

		<?= Html::textarea($name, $value ?? '', $options + ['rows' => 4]) ?>

	<?php else: ?>

		<?= Html::input($type ?? 'text', $name, $value ?? '', $options) ?>

	<?php endif ?>

	<?php if (!empty($hint)): ?>
		<small><?= Html::encode($hint) ?>
		</small>

	<?php endif ?>
</div>
