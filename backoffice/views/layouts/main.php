<?php

use backoffice\assets\AppAsset;
use backoffice\assets\BehaviorAsset;
use common\assets\UiAsset;
use common\widgets\Toast;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var string $content */

AppAsset::register($this);
UiAsset::register($this);
BehaviorAsset::register($this);
?>

<?php $this->beginPage() ?>

<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>
" data-theme="light">
	<head>
	<meta charset="<?= Yii::$app->charset ?>
">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?= Html::csrfMetaTags() ?>
			<title><?= Html::encode($this->title) ?>
			</title>

			<?php $this->head() ?>
		</head>
		<body>
	<a class="skip-link" href="#main-content">Ir para o conteúdo</a>

			<?php $this->beginBody() ?>
			<div class="backoffice-shell">
		<?= $this->render('../components/sidebar') ?>
				<div class="workspace">
			<?= $this->render('../components/header') ?>

					<?= $content ?>
				</div>
	</div>
			<?= Toast::widget() ?>

			<?php $this->endBody() ?>
		</body>
	</html>

	<?php $this->endPage() ?>
