<?php

use common\assets\UiAsset;
use common\widgets\Toast;
use frontoffice\assets\AppAsset;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var string $content */

AppAsset::register($this);
UiAsset::register($this);
?>
<?php $this->beginPage() ?>

<!DOCTYPE html>
<html lang="pt-PT" data-theme="dark">
	<head>
	<meta charset="<?= Yii::$app->charset ?>
">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?= Html::csrfMetaTags() ?>
			<title><?= Html::encode($this->title) ?>
· CineLis</title>

			<?php $this->head() ?>
		</head>
		<body class="app">
	<?php $this->beginBody() ?>
			<a class="skip" href="#main-content">Saltar para o conteúdo</a>
			<?= $this->render('../components/header') ?>
			<main id="main-content" class="main" tabindex="-1">
		<?= $content ?>
			</main>
			<?= $this->render('../components/footer') ?>

			<?= $this->render('../components/mobile-nav') ?>

			<?= Toast::widget() ?>

			<?php $this->endBody() ?>
		</body>
	</html>

	<?php $this->endPage() ?>
