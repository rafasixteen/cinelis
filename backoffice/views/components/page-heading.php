<?php

use common\widgets\Button;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var string $title */
/** @var string|null $section */
/** @var string|null $description */
/** @var array|null $action */

$this->title = $title.' · CineLis Backoffice';
$this->params['section'] = $section ?? $title;
?>

<header class="heading">
	<div>
		<h1><?= Html::encode($title) ?>
		</h1>

		<?php if (!empty($description)): ?>
			<p><?= Html::encode($description) ?>
			</p>

		<?php endif ?>
	</div>

	<?php if (isset($action)): ?>

		<?= Button::widget([
			'label' => $action['label'],
			'tag' => 'a',
			'url' => $action['url'],
			'icon' => $action['icon'] ?? 'plus',
		]) ?>

	<?php endif ?>
</header>
